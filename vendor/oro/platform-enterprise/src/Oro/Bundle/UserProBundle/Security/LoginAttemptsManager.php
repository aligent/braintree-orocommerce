<?php

namespace Oro\Bundle\UserProBundle\Security;

use Psr\Log\LoggerInterface;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserProBundle\Mailer\Processor;

class LoginAttemptsManager
{
    /** @var LoginAttemptsProvider */
    protected $attemptsProvider;

    /** @var UserManager */
    protected $userManager;

    /** @var Processor */
    protected $mailProcessor;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param LoginAttemptsProvider $attemptsProvider
     * @param UserManager           $userManager
     * @param Processor             $mailProcessor
     * @param LoggerInterface       $logger
     */
    public function __construct(
        LoginAttemptsProvider $attemptsProvider,
        UserManager $userManager,
        Processor $mailProcessor,
        LoggerInterface $logger
    ) {
        $this->attemptsProvider = $attemptsProvider;
        $this->userManager      = $userManager;
        $this->mailProcessor    = $mailProcessor;
        $this->logger           = $logger;
    }

    /**
     * @param User $user
     */
    public function trackLoginSuccess(User $user)
    {
        $this->resetFailedLoginCounters($user);
    }

    /**
     * Update login counter and deactivate the user when limits are exceeded
     *
     * @param User $user
     */
    public function trackLoginFailure(User $user)
    {
        if (!$this->attemptsProvider->hasLimit()) {
            return;
        }

        $user->setFailedLoginCount($user->getFailedLoginCount() + 1);

        if ($this->attemptsProvider->hasReachedLimit($user)) {
            $this->userManager->setAuthStatus($user, AuthStatus::LOCKED);
            $this->userManager->updateUser($user, true);
            try {
                $this->mailProcessor->sendAutoDeactivateEmail($user, $this->attemptsProvider->getLimit());
            } catch (\Swift_SwiftException $exception) {
                $this->logger->error('Unable to send auto deactivation email', ['exception' => $exception]);
            }

            return;
        }

        $this->userManager->updateUser($user);
    }

    /**
     * @param User $user
     */
    protected function resetFailedLoginCounters(User $user)
    {
        if ($this->attemptsProvider->hasLimit()) {
            $user->setFailedLoginCount(0);
            $this->userManager->updateUser($user);
        }
    }
}
