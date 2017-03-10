<?php

namespace Oro\Bundle\UserProBundle\Command;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\NotificationBundle\Model\EmailNotification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PasswordExpirationNotificationCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const EMAIL_TEMPLATE = 'mandatory_password_change';

    /**
     * Run command at 00:00 every day.
     *
     * @return string
     */
    public function getDefaultDefinition()
    {
        return '0 0 * * *';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $notificationDays =
            $this->getContainer()->get('oro_userpro.provider.password_change_period_config_provider')
            ->getNotificationDays();

        return (0 !== count($notificationDays));
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:cron:password:notify-expiring')
            ->setDescription('Send email notification reminder about expiring passwords to users');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $mailManager = $container->get('oro_notification.manager.email_notification');
        $doctrine = $container->get('oro_entity.doctrine_helper');
        $notificationDays = $container->get('oro_userpro.provider.password_change_period_config_provider')
            ->getNotificationDays();
        if (0 === count($notificationDays)) {
            throw new \InvalidArgumentException('No notification days are configured');
        }

        $utc = new \DateTimeZone('UTC');

        /** @var QueryBuilder $builder */
        $builder = $doctrine->getEntityRepository('OroUserBundle:User')->findEnabledUsersQB();

        $conditions = [];

        foreach ($notificationDays as $index => $day) {
            $from = new \DateTime('+' . $day . ' day midnight', $utc);
            $to = new \DateTime('+' . $day . ' day 23:59:59', $utc);
            $conditions[] = $builder->expr()->between(
                'u.password_expires_at',
                ':from' . $index,
                ':to' . $index
            );
            $builder->setParameter('from' . $index, $from, Type::DATETIME);
            $builder->setParameter('to' . $index, $to, Type::DATETIME);
        }

        $builder->andWhere($builder->expr()->orX()->addMultiple($conditions));

        $iteratorResult = $builder->getQuery()->iterate();

        $template = $doctrine->getEntityRepository('OroEmailBundle:EmailTemplate')
            ->findOneByName(self::EMAIL_TEMPLATE);

        if (!$template) {
            $output->writeln('<error>Cannot find notification template</error>');

            return;
        }

        $usersCount = 0;
        while ($results = $iteratorResult->next()) {
            $user = $results[0];
            $notification = new EmailNotification($template, [$user->getEmail()]);
            $mailManager->process($user, [$notification]);
            $usersCount++;
        }

        $output->writeln(
            sprintf(
                '<info>Password expiration notification has been enqueued for %d users</info>',
                $usersCount
            )
        );
    }
}
