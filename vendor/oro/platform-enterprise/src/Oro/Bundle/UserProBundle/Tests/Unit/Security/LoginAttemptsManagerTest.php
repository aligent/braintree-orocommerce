<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Security;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserProBundle\Mailer\Processor;
use Oro\Bundle\UserProBundle\Security\AuthStatus;
use Oro\Bundle\UserProBundle\Security\LoginAttemptsManager;
use Oro\Bundle\UserProBundle\Security\LoginAttemptsProvider;
use Oro\Bundle\UserProBundle\Tests\Unit\Entity\Stub\User;
use Oro\Component\Testing\Unit\Entity\Stub\StubEnumValue;
use Psr\Log\LoggerInterface;

class LoginAttemptsManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testDoesNotDisableUserOnRemainingAttempts()
    {
        $user = $this->getUser();
        $manager = new LoginAttemptsManager(
            $this->getLoginAttemptsProvider(3),
            $this->getUserManager($user),
            $this->getMailProcessor(),
            $this->getLogger()
        );

        $manager->trackLoginFailure($user);

        $this->assertTrue($user->isEnabled());
    }

    public function testIncrementCounterOnFailedLogin()
    {
        $user = $this->getUser(5);
        $manager = new LoginAttemptsManager(
            $this->getLoginAttemptsProvider(10),
            $this->getUserManager($user),
            $this->getMailProcessor(),
            $this->getLogger()
        );

        $manager->trackLoginFailure($user);

        $this->assertSame(6, $user->getFailedLoginCount());
    }

    public function testDeactivateUserOnExceededLimit()
    {
        $user = $this->getUser(4);
        $manager = new LoginAttemptsManager(
            $this->getLoginAttemptsProvider(5),
            $this->getUserManager($user),
            $this->getMailProcessor(1),
            $this->getLogger()
        );

        $manager->trackLoginFailure($user);

        $this->assertTrue($user->getAuthStatus()->getId() === AuthStatus::LOCKED);
    }

    public function testResetCounterOnSuccessfulLogin()
    {
        $user = $this->getUser(5);
        $manager = new LoginAttemptsManager(
            $this->getLoginAttemptsProvider(30),
            $this->getUserManager($user),
            $this->getMailProcessor(),
            $this->getLogger()
        );

        $manager->trackLoginSuccess($user);

        $this->assertSame(0, $user->getFailedLoginCount());
    }

    /**
     * @param int $loginFails
     *
     * @return User
     */
    private function getUser($loginFails = 0)
    {
        $user = new User();
        $user->setEnabled(true);
        $user->setAuthStatus(new StubEnumValue('active', 'active'));
        $user->setUsername('john');
        $user->setFailedLoginCount($loginFails);

        return $user;
    }

    /**
     * @param int $limit
     *
     * @return LoginAttemptsProvider
     */
    private function getLoginAttemptsProvider($limit = 0)
    {
        $provider = $this->getMockBuilder(LoginAttemptsProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getLimit',
                    'hasLimit',
                ]
            )
            ->getMock();

        $provider->expects($this->any())
            ->method('getLimit')
            ->willReturn($limit);

        $provider->expects($this->any())
            ->method('hasLimit')
            ->willReturn(0 !== $limit);

        return $provider;
    }

    /**
     * @param User|null $user
     *
     * @return UserManager
     */
    private function getUserManager(User $user = null)
    {
        $manager = $this->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('findUserByUsernameOrEmail')
            ->willReturn($user);
        $manager->expects($this->any())
            ->method('setAuthStatus')
            ->willReturnCallback(
                function ($user, $status) {
                    $user->setAuthStatus(new StubEnumValue($status, $status));
                }
            );

        return $manager;
    }

    /**
     * @param int $nbEmails
     *
     * @return Processor
     */
    private function getMailProcessor($nbEmails = 0)
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processor->expects($this->exactly($nbEmails))
            ->method('sendAutoDeactivateEmail');

        return $processor;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }
}
