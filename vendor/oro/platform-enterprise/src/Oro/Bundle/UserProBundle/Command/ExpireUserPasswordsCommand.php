<?php

namespace Oro\Bundle\UserProBundle\Command;

use Doctrine\DBAL\Types\Type;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\UserBundle\Entity\Repository\UserRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserProBundle\Async\Topics;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireUserPasswordsCommand extends ContainerAwareCommand implements CronCommandInterface
{
    /**
     * Run command every hour
     *
     * @return string
     */
    public function getDefaultDefinition()
    {
        return '0 * * * *';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        /** @var UserRepository $repo */
        $repo = $this->getContainer()->get('doctrine')->getManagerForClass(User::class)->getRepository(User::class);

        $expireAt = new \DateTime('now', new \DateTimeZone('UTC'));

        $qb = $repo->createQueryBuilder('u');

        $qb->select('COUNT(u.id)')
            ->where('u.enabled = :enabled')
            ->setParameter('enabled', true)
            ->andWhere('u.password_expires_at <= :expiresAt')
            ->andWhere('IDENTITY(u.auth_status) <> :authStatus')
            ->setParameter('expiresAt', $expireAt, Type::DATETIME)
            ->setParameter('authStatus', 'expired', Type::STRING);


        $count = $qb->getQuery()->getSingleScalarResult();

        return ($count > 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:cron:password:expire-passwords')
            ->setDescription(
                'Sets users with expired passwords into \'Reset password\' state and sends them a notification'
            )
            ->setHelp(
                'Command produces messages to MQ to change AuthStatus to \'expired\' for users' .
                ' with expired passwords. By default will produce one message per user.' . PHP_EOL .
                ' Set `--batch-size` to process multiple users with 1 message.' . PHP_EOL .
                ' Note: Users are not affected before MQ messages are processed.'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $producer = $container->get('oro_message_queue.client.message_producer');
        /** @var UserRepository $repo */
        $repo = $container->get('doctrine')->getManagerForClass(User::class)->getRepository(User::class);

        $expireAt = new \DateTime('now', new \DateTimeZone('UTC'));

        $qb = $repo->findEnabledUsersQB();

        $qb->andWhere('u.password_expires_at <= :expiresAt')
            ->andWhere('IDENTITY(u.auth_status) <> :authStatus')
            ->setParameter('expiresAt', $expireAt, Type::DATETIME)
            ->setParameter('authStatus', 'expired', Type::STRING);

        $result = $qb->getQuery()->getArrayResult();
        $userIds = array_column($result, 'id');

        foreach ($userIds as $userId) {
            $producer->send(Topics::EXPIRE_USER_PASSWORD, ['userId' => $userId]);
        }

        $output->writeln(sprintf('<info>Password expiration has been queued for %d users.</info>', count($userIds)));
    }
}
