<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Security;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserProBundle\Manager\ClientDataManager;
use Oro\Bundle\UserProBundle\Manager\TwoFactorCodeManager;
use Oro\Bundle\UserProBundle\Security\TwoFactorAuthProvider;
use Oro\Bundle\UserProBundle\Security\TwoFactorToken;

class TwoFactorAuthProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldSuccessWithValidCredentials()
    {
        $user = $this->getUser();
        $provider = $this->getAuthProvider('code123', $user, 1, 1);
        $token = $this->getToken($user, 'code123');

        $authenticatedToken = $provider->authenticate($token);

        $this->assertTrue($authenticatedToken->isAuthenticated());
        $this->assertSame($user, $authenticatedToken->getUser());
    }

    public function testSupportTwoFactorToken()
    {
        $user = $this->getUser();
        $provider = $this->getAuthProvider('code123', $user);
        $token = $this->getToken($user, 'code123');

        $this->assertTrue($provider->supports($token));
    }

    public function testDoesNotSupportUsernamePasswordToken()
    {
        $user = $this->getUser();
        $provider = $this->getAuthProvider('code123', $user);
        $token = new UsernamePasswordToken($user, 'pass123', 'form-login');

        $this->assertFalse($provider->supports($token));
    }

    public function testShouldSkipAuthenticatedToken()
    {
        $user = $this->getUser();
        $provider = $this->getAuthProvider('code', $user, 0, 0);
        $token = $this->getToken($user, 'code123', true);

        $authenticatedToken = $provider->authenticate($token);

        $this->assertSame($token, $authenticatedToken);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testShouldFailWithEmptyCredentials()
    {
        $user = $this->getUser();
        $provider = $this->getAuthProvider('code', $user, 0, 0);
        $token = $this->getToken($user, '');

        $authenticatedToken = $provider->authenticate($token);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testShouldFailWithUnkownUserCode()
    {
        $user = $this->getUser();
        $provider = $this->getAuthProvider(null, $user, 0, 0);
        $token = $this->getToken($user, 'code123');

        $authenticatedToken = $provider->authenticate($token);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testShouldFailWhenCredentialsMismatch()
    {
        $user = $this->getUser();
        $provider = $this->getAuthProvider('code', $user, 0, 0);
        $token = $this->getToken($user, 'code123');

        $authenticatedToken = $provider->authenticate($token);
    }

    /**
     * @param  string $code
     * @param  int $numberOfInvalidateCalls
     * @return TwoFactorCodeManager
     */
    private function getCodeManager($code, $numberOfInvalidateCalls)
    {
        $manager = $this->getMockBuilder(TwoFactorCodeManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('find')
            ->willReturn($code);

        $manager->expects($this->exactly($numberOfInvalidateCalls))
            ->method('invalidate');

        return $manager;
    }

    /**
     * @param  int $numberOfAddCalls
     * @return ClientDataManager
     */
    private function getClientDataManager($numberOfAddCalls)
    {
        $manager = $this->getMockBuilder(ClientDataManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->exactly($numberOfAddCalls))
            ->method('addClientData');

        return $manager;
    }

    /**
     * @param  UserInterface $user
     * @return UserProviderInterface
     */
    private function getUserProvider(UserInterface $user)
    {
        $provider = $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->any())
            ->method('refreshUser')
            ->willReturn($user);

        return $provider;
    }

    /**
     * @param  string        $code
     * @param  UserInterface $user
     * @param  integer       $nbOfInvalidateCalls
     * @param  integer       $nbOfAddCalls
     * @return TwoFactorAuthProvider
     */
    private function getAuthProvider($code, UserInterface $user, $nbOfInvalidateCalls = 0, $nbOfAddCalls = 0)
    {
        return new TwoFactorAuthProvider(
            $this->getCodeManager($code, $nbOfInvalidateCalls),
            $this->getClientDataManager($nbOfAddCalls),
            $this->getUserProvider($user),
            'two-factor'
        );
    }

    /**
     * @param  UserInterface $user
     * @param  string        $credentials
     * @param  boolean       $isAuthenticated
     * @return TwoFactorToken
     */
    private function getToken(UserInterface $user, $credentials, $isAuthenticated = false)
    {
        $token = new TwoFactorToken(
            new UsernamePasswordToken($user, 'pass123', 'form-login'),
            new \DateTime(),
            $credentials
        );

        $token->setAuthenticated($isAuthenticated);

        return $token;
    }

    /**
     * @return User
     */
    private function getUser()
    {
        return new User();
    }
}
