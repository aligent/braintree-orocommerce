<?php

namespace Oro\Bundle\UserProBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserProBundle\Security\AuthStatus;

class UnlockUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('oro:user:unlock')
            ->setDescription('Unlocks the account of a given user.')
            ->setHelp('The command allows you to activate a user whose account has been locked.')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $userManager = $this->getContainer()->get('oro_user.manager');
        $user = $userManager->findUserByUsername($username);

        if (!$user) {
            $output->writeln(
                sprintf(
                    '<error>Could not find a user with usernme %s.</error>',
                    $username
                )
            );

            return 1;
        }

        if ($user->getAuthStatus()->getId() !== AuthStatus::LOCKED) {
            $output->writeln(
                sprintf(
                    '<error>The account of user %s is not locked.</error>',
                    $user->getUsername()
                )
            );

            return 1;
        }

        $userManager->setAuthStatus($user, UserManager::STATUS_ACTIVE);
        $userManager->updateUser($user);

        $output->writeln(
            sprintf(
                '<info>The account of user <comment>%s</comment> has been unlocked.</info>',
                $user->getUsername()
            )
        );

        return 0;
    }
}
