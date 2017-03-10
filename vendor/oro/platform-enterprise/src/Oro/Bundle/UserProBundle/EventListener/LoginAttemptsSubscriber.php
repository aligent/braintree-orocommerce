<?php

namespace Oro\Bundle\UserProBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

use Oro\Bundle\UserBundle\Entity\BaseUserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserProBundle\Security\Exception\BadCredentialsException as BadCredentialsExceptionWithLoginAttempts;
use Oro\Bundle\UserProBundle\Security\LoginAttemptsManager;
use Oro\Bundle\UserProBundle\Security\LoginAttemptsProvider;
use Oro\Bundle\UserProBundle\Security\TwoFactorToken;

class LoginAttemptsSubscriber implements EventSubscriberInterface
{
    /** @var BaseUserManager */
    protected $userManager;

    /** @var LoginAttemptsManager $attemptsManager */
    protected $attemptsManager;

    /** @var LoginAttemptsProvider $attemptsProvider */
    protected $attemptsProvider;

    /**
     * @param BaseUserManager       $userManager
     * @param LoginAttemptsManager  $attemptsManager
     * @param LoginAttemptsProvider $attemptsProvider
     */
    public function __construct(
        BaseUserManager $userManager,
        LoginAttemptsManager $attemptsManager,
        LoginAttemptsProvider $attemptsProvider
    ) {
        $this->userManager      = $userManager;
        $this->attemptsManager  = $attemptsManager;
        $this->attemptsProvider = $attemptsProvider;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
            SecurityEvents::INTERACTIVE_LOGIN            => 'onInteractiveLogin',
        ];
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        if (is_string($user)) {
            $user = $this->userManager->findUserByUsernameOrEmail($user);
        }

        if ($user instanceof User) {
            $this->attemptsManager->trackLoginFailure($user);
            $this->replaceException($event->getAuthenticationException(), $user);
        }
    }

    /**
     * @param  InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        if ($token instanceof TwoFactorToken) {
            return;
        }

        if ($user instanceof User) {
            $this->attemptsManager->trackLoginSuccess($user);
        }
    }

    /**
     * Replace original BadCredentialsException
     * with exception containing remaining attempts parameter
     *
     * @param  AuthenticationException $exception
     * @param  User                    $user
     */
    protected function replaceException(AuthenticationException $exception, User $user)
    {
        if (!$this->attemptsProvider->hasLimit()) {
            return;
        }

        if ($exception instanceof BadCredentialsException) {
            $error = new BadCredentialsExceptionWithLoginAttempts(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getPrevious()
            );

            $error->setRemainingAttempts($this->attemptsProvider->getRemaining($user));

            throw $error;
        }
    }
}
