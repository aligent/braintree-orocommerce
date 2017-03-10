<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\OrganizationProBundle\Helper\OrganizationProHelper;
use Oro\Bundle\UserProBundle\Form\Type\RoleOrganizationSelectType;
use Oro\Bundle\UserProBundle\Helper\UserProHelper;

class RoleOrganizationSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RoleOrganizationSelectType
     */
    protected $roleOrganizationSelectType;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserProHelper
     */
    protected $userHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OrganizationProHelper
     */
    protected $organizationHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenInterface
     */
    protected $token;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface'
        );

        $this->userHelper = $this->getMockBuilder('Oro\Bundle\UserProBundle\Helper\UserProHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->organizationHelper = $this->getMockBuilder(
            'Oro\Bundle\OrganizationProBundle\Helper\OrganizationProHelper'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->roleOrganizationSelectType = new RoleOrganizationSelectType(
            $this->tokenStorage,
            $this->userHelper,
            $this->organizationHelper
        );
    }

    protected function tearDown()
    {
        unset(
            $this->tokenStorage,
            $this->userHelper,
            $this->token,
            $this->roleOrganizationSelectType
        );
    }

    public function testConfigureOptionsWhenUserIsAssignedToGlobalOrganization()
    {
        $this->organizationHelper->expects($this->once())
            ->method('isGlobalOrganizationExists')
            ->will($this->returnValue(true));

        $this->userHelper->expects($this->once())
            ->method('isUserAssignedToGlobalOrganization')
            ->will($this->returnValue(true));

        /** @var \PHPUnit_Framework_MockObject_MockObject|OptionsResolver $resolver */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');

        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->roleOrganizationSelectType->configureOptions($resolver);
    }

    /**
     * @dataProvider dataProviderForConfigureOptions
     */
    public function testConfigureOptionsAddRequired(
        $isGlobalOrganizationExists,
        $isUserAssignedToGlobalOrganization,
        $expectRequired
    ) {
        $this->organizationHelper->expects($this->atLeastOnce())
            ->method('isGlobalOrganizationExists')
            ->will($this->returnValue($isGlobalOrganizationExists));

        if ($isGlobalOrganizationExists) {
            $this->userHelper->expects($this->once())
                ->method('isUserAssignedToGlobalOrganization')
                ->will($this->returnValue($isUserAssignedToGlobalOrganization));
        }

        /** @var \PHPUnit_Framework_MockObject_MockObject|OptionsResolver $resolver */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');

        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                $this->callback(
                    function (array $defaults) use ($expectRequired) {
                        if ($expectRequired) {
                            $this->assertArrayHasKey('constraints', $defaults, 'Field is not required');
                            $this->assertArrayHasKey('attr', $defaults, 'Field is not required');
                        } else {
                            $this->assertArrayNotHasKey('constraints', $defaults, 'Field is required');
                            $this->assertArrayNotHasKey('attr', $defaults, 'Field is required');
                        }

                        return true;
                    }
                )
            );

        $this->roleOrganizationSelectType->configureOptions($resolver);
    }

    public function dataProviderForConfigureOptions()
    {
        return [
            'not required when there is no global organization' => [
                'global organization exists' => false,
                'user assigned to global organization' => false,
                'expectRequired' => false,
            ],
            'not required when user assigned to global organization' => [
                'global organization exists' => true,
                'user assigned to global organization' => true,
                'expect required' => false,
            ],
            'required when user is not assigned to global organization' => [
                'global organization exists' => true,
                'user assigned to global organization' => false,
                'expect required' => true,
            ],
        ];
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_jqueryselect2_hidden', $this->roleOrganizationSelectType->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_userpro_role_organization_select', $this->roleOrganizationSelectType->getName());
    }
}
