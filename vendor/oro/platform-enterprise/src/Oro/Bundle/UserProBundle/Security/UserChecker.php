<?php

namespace Oro\Bundle\UserProBundle\Security;

use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;

/**
 * Decorates and adds additional checks in UserChecker
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * @var UserCheckerInterface
     */
    private $userCheckerInner;

    /**
     * @param UserCheckerInterface $userCheckerInner
     */
    public function __construct(UserCheckerInterface $userCheckerInner)
    {
        $this->userCheckerInner = $userCheckerInner;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        return $this->userCheckerInner->checkPostAuth($user);
    }

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        if ($user instanceof User && null !== $user->getAuthStatus()) {
            $authStatus = $user->getAuthStatus()->getId();

            if ($authStatus === AuthStatus::LOCKED) {
                $exception = new LockedException('Account is locked.');
                $exception->setUser($user);

                throw $exception;
            }

            if ($authStatus === UserManager::STATUS_EXPIRED || $this->isPasswordExpired($user)) {
                $exception = new CredentialsExpiredException('Password expired.');
                $exception->setUser($user);

                throw $exception;
            }
        }

        return $this->userCheckerInner->checkPreAuth($user);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    protected function isPasswordExpired(User $user)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        return $user->getPasswordExpiresAt() && $user->getPasswordExpiresAt() < $now;
    }
}
