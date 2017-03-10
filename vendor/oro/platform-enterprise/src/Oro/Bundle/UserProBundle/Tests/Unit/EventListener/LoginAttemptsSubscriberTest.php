<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserProBundle\EventListener\LoginAttemptsSubscriber;
use Oro\Bundle\UserProBundle\Security\LoginAttemptsManager;
use Oro\Bundle\UserProBundle\Security\LoginAttemptsProvider;
use Oro\Bundle\UserProBundle\Tests\Unit\Entity\Stub\User;

class LoginAttemptsSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testDoesNotTrackUnknownUsernames()
    {
        $subscriber = $this->getSubscriber(null);
        $event = $this->getFailureEvent('john');

        $subscriber->onAuthenticationFailure($event);
    }

    public function testDoesNotTrackUsersWithoutFailedLoginInfoOnFailure()
    {
        $subscriber = $this->getSubscriber(new \stdClass());
        $event = $this->getFailureEvent('john');

        $subscriber->onAuthenticationFailure($event);
    }

    public function testDoesNotTrackUsersWithoutFailedLoginInfoOnSuccess()
    {
        $subscriber = $this->getSubscriber();
        $event = $this->getInteractiveLoginEvent(new \stdClass());

        $subscriber->onInteractiveLogin($event);
    }

    public function shouldTrackFailures()
    {
        $subscriber = $this->getSubscriber(new User(), 1);
        $event = $this->getFailureEvent('john');

        $subscriber->onAuthenticationFailure($event);
    }

    public function testShouldTrackInteractiveLogins()
    {
        $subscriber = $this->getSubscriber(new User(), 0, 1);
        $event = $this->getInteractiveLoginEvent(new User());

        $subscriber->onInteractiveLogin($event);
    }

    /**
     * @expectedException \Oro\Bundle\UserProBundle\Security\Exception\BadCredentialsException
     */
    public function testAddRemainingAttemptsToException()
    {
        $subscriber = $this->getSubscriber(new User(), 1, 0, 10);
        $event = $this->getFailureEvent('john', new BadCredentialsException());

        $subscriber->onAuthenticationFailure($event);
    }

    /**
     * @param object|null $user
     * @return UserManager
     */
    private function getUserManager($user = null)
    {
        $manager = $this->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('findUserByUsernameOrEmail')
            ->willReturn($user);

        return $manager;
    }

    /**
     * @param int $failCalls
     * @param int $successCalls
     * @return LoginAttemptsManager
     */
    private function getAttemptsManager($failCalls = 0, $successCalls = 0)
    {
        $manager = $this->getMockBuilder(LoginAttemptsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->exactly($failCalls))
            ->method('trackLoginFailure');

        $manager->expects($this->exactly($successCalls))
            ->method('trackLoginSuccess');

        return $manager;
    }

    /**
     * @param string $username
     * @return AuthenticationFailureEvent
     */
    private function getFailureEvent($username, $exception = null)
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($username);

        $event = $this->getMockBuilder(AuthenticationFailureEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getAuthenticationException')
            ->willReturn($exception);

        $event->expects($this->any())
            ->method('getAuthenticationToken')
            ->willReturn($token);

        return $event;
    }

    /**
     * @param object $user
     * @return InteractiveLoginEvent
     */
    private function getInteractiveLoginEvent($user)
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $event = $this->getMockBuilder(InteractiveLoginEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getAuthenticationToken')
            ->willReturn($token);

        return $event;
    }

    /**
     * @param  int $remaining
     * @return LoginAttemptsProvider
     */
    private function getAttemptsProvider($remaining)
    {
        $provider = $this->getMockBuilder(LoginAttemptsProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->any())
            ->method('hasLimit')
            ->willReturn($remaining > 0);

        $provider->expects($this->any())
            ->method('getRemaining')
            ->willReturn($remaining);

        return $provider;
    }

    /**
     * @param object|null $user
     * @param int $failCalls
     * @param int $successCalls
     * @return LoginAttemptsSubscriber
     */
    private function getSubscriber($user = null, $failCalls = 0, $successCalls = 0, $remainingAttempts = 0)
    {
        return new LoginAttemptsSubscriber(
            $this->getUserManager($user),
            $this->getAttemptsManager($failCalls, $successCalls),
            $this->getAttemptsProvider($remainingAttempts)
        );
    }
}
