<?php

namespace Oro\Bundle\LDAPBundle\Tests\Unit\Security;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationTokenFactoryInterface;
use Oro\Bundle\LDAPBundle\Security\LdapAuthenticationProvider;
use Oro\Bundle\LDAPBundle\Security\LdapAuthenticator;
use Oro\Bundle\LDAPBundle\Tests\Unit\Stub\TestingUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LdapAuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{
    private $providerKey = 'oro_ldap';

    /** @var \PHPUnit_Framework_MockObject_MockObject|UserProviderInterface */
    private $userProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LdapAuthenticator */
    private $ldapAuthenticator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LdapAuthenticationProvider */
    private $ldapProvider;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|UsernamePasswordOrganizationTokenFactoryInterface */
    protected $tokenFactory;

    public function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|UserCheckerInterface $userChecker */
        $userChecker = $this->createMock('Symfony\Component\Security\Core\User\UserCheckerInterface');

        $this->userProvider = $this->createMock('Symfony\Component\Security\Core\User\UserProviderInterface');

        /** @var \PHPUnit_Framework_MockObject_MockObject|EncoderFactoryInterface $encoderFactory */
        $encoderFactory = $this->createMock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');

        $this->ldapAuthenticator= $this->getMockBuilder('Oro\Bundle\LDAPBundle\Security\LdapAuthenticator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenFactory = $this->createMock(
            'Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationTokenFactoryInterface'
        );

        $this->ldapProvider = new LdapAuthenticationProvider(
            $this->userProvider,
            $userChecker,
            $this->providerKey,
            $encoderFactory,
            true,
            $this->ldapAuthenticator
        );

        $this->ldapProvider->setTokenFactory($this->tokenFactory);
    }

    public function testTokenShouldBeAuthenticated()
    {
        $testRole1Label = 'test role 1';
        $testRole1 = $this->prepareRoleStub($testRole1Label);
        $testRole2Label = 'test role 2';
        $testRole2 = $this->prepareRoleStub($testRole2Label);
        $expectedRoles = [$testRole1, $testRole2];
        $expectedCredentials = 'credentials';
        $token = new UsernamePasswordToken('user', '' . $expectedCredentials . '', $this->providerKey);

        $organization = new Organization();
        $organization->setEnabled(true);

        $user = new TestingUser();
        $user->addOrganization($organization);
        $user->setRoles($expectedRoles);

        $this->userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('user')
            ->will($this->returnValue($user));

        $this->ldapAuthenticator->expects($this->once())
            ->method('check')
            ->will($this->returnValue(true));

        $expectedToken = $this->getMockBuilder(
            'Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->with(
                $user,
                $expectedCredentials,
                $this->providerKey,
                $organization,
                $expectedRoles
            )->willReturn($expectedToken);

        $resultToken = $this->ldapProvider->authenticate($token);

        $this->assertInstanceOf(
            'Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken',
            $resultToken
        );
        $this->assertSame($expectedToken, $resultToken);
    }

    /**
     * @param string $roleLabel
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareRoleStub($roleLabel)
    {
        $role = $this->createPartialMock(
            'Symfony\Component\Security\Core\Role\RoleInterface',
            ['__toString', 'getRole']
        );
        $role->expects($this->any())
            ->method('getRole')
            ->willReturn($roleLabel);
        $role->expects($this->any())
            ->method('__toString')
            ->willReturn($roleLabel);

        return $role;
    }
}
