<?php

namespace Oro\Bundle\SecurityProBundle\Tests\Unit\Acl\Persistence;

use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

use Oro\Bundle\SecurityProBundle\Acl\Domain\BusinessUnitSecurityIdentity;
use Oro\Bundle\SecurityProBundle\Acl\Domain\OrganizationSecurityIdentity;
use Oro\Bundle\SecurityProBundle\Acl\Persistence\BaseAclManager;

class BaseAclManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractAclManager */
    private $manager;

    protected function setUp()
    {
        $this->manager = new BaseAclManager();
    }

    public function testGetSid()
    {
        $this->assertEquals(
            new RoleSecurityIdentity('ROLE_TEST'),
            $this->manager->getSid('ROLE_TEST')
        );

        $src = $this->createMock('Symfony\Component\Security\Core\Role\RoleInterface');
        $src->expects($this->once())
            ->method('getRole')
            ->will($this->returnValue('ROLE_TEST'));
        $this->assertEquals(
            new RoleSecurityIdentity('ROLE_TEST'),
            $this->manager->getSid($src)
        );

        $src = $this->createMock('Symfony\Component\Security\Core\User\UserInterface');
        $src->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('Test'));
        $this->assertEquals(
            new UserSecurityIdentity('Test', get_class($src)),
            $this->manager->getSid($src)
        );

        $user = $this->createMock('Symfony\Component\Security\Core\User\UserInterface');
        $user->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('Test'));
        $src = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $src->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->assertEquals(
            new UserSecurityIdentity('Test', get_class($user)),
            $this->manager->getSid($src)
        );

        $businessUnit = $this->createMock('Oro\Bundle\OrganizationBundle\Entity\BusinessUnitInterface');
        $businessUnit->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->assertEquals(
            new BusinessUnitSecurityIdentity(1, get_class($businessUnit)),
            $this->manager->getSid($businessUnit)
        );

        $organization = $this->createMock('Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface');
        $organization->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->assertEquals(
            new OrganizationSecurityIdentity(1, get_class($organization)),
            $this->manager->getSid($organization)
        );

        $this->expectException('\InvalidArgumentException');
        $this->manager->getSid(new \stdClass());
    }
}
