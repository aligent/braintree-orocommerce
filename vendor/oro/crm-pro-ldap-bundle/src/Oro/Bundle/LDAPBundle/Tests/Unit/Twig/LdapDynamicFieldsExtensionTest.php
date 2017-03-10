<?php

namespace Oro\Bundle\LDAPBundle\Tests\Unit\Twig;

use Oro\Bundle\EntityExtendBundle\Twig\AbstractDynamicFieldsExtension;
use Oro\Bundle\LDAPBundle\Twig\LdapDynamicFieldsExtension;

class LdapDynamicFieldsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Oro\Bundle\EntityExtendBundle\Twig\DynamicFieldsExtension|\PHPUnit_Framework_MockObject_MockObject */
    protected $baseExtension;

    /** @var \Oro\Bundle\SecurityBundle\SecurityFacade|\PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    /** @var LdapDynamicFieldsExtension */
    protected $dynamicFieldsExtension;

    public function setUp()
    {
        $this->baseExtension = $this->getMockBuilder(AbstractDynamicFieldsExtension::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dynamicFieldsExtension = new LdapDynamicFieldsExtension($this->baseExtension, $this->securityFacade);
    }

    public function testGetFieldsWhenLdapFieldNotPresent()
    {
        $base = [
            'some_field'  => [
                'some_field_property',
                'other_field_property',
            ],
            'other_field' => [
                'first_field_property',
                'second_field_property',
            ],
        ];

        $expected = [
            'some_field'  => [
                'some_field_property',
                'other_field_property',
            ],
            'other_field' => [
                'first_field_property',
                'second_field_property',
            ],
        ];

        $entity = $this->createMock('Oro\Bundle\UserBundle\Entity\User');

        $this->baseExtension->expects($this->once())
            ->method('getFields')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($base));

        $result = $this->dynamicFieldsExtension->getFields($entity);

        $this->assertEquals($expected, $result);
    }

    public function testGetFieldsWhenAccessGranted()
    {
        $base = [
            'some_field'  => [
                'some_field_property',
                'other_field_property',
            ],
            'other_field' => [
                'first_field_property',
                'second_field_property',
            ],
            'ldap_distinguished_names' => [
                'properties...'
            ],
        ];

        $expected = [
            'some_field'  => [
                'some_field_property',
                'other_field_property',
            ],
            'other_field' => [
                'first_field_property',
                'second_field_property',
            ],
            'ldap_distinguished_names' => [
                'properties...'
            ],
        ];

        $entity = $this->createMock('Oro\Bundle\UserBundle\Entity\User');

        $this->baseExtension->expects($this->any())
            ->method('getFields')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($base));

        $this->securityFacade->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));

        $result = $this->dynamicFieldsExtension->getFields($entity);

        $this->assertEquals($expected, $result);
    }

    public function testGetFieldsWhenAccessNotGranted()
    {
        $base = [
            'some_field'  => [
                'some_field_property',
                'other_field_property',
            ],
            'other_field' => [
                'first_field_property',
                'second_field_property',
            ],
            'ldap_distinguished_names' => [
                'properties...'
            ],
        ];

        $expected = [
            'some_field'  => [
                'some_field_property',
                'other_field_property',
            ],
            'other_field' => [
                'first_field_property',
                'second_field_property',
            ],
        ];

        $entity = $this->createMock('Oro\Bundle\UserBundle\Entity\User');

        $this->baseExtension->expects($this->any())
            ->method('getFields')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($base));

        $this->securityFacade->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(false));

        $result = $this->dynamicFieldsExtension->getFields($entity);

        $this->assertEquals($expected, $result);
    }

    public function testGetName()
    {
        $this->assertEquals(AbstractDynamicFieldsExtension::NAME, $this->dynamicFieldsExtension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->dynamicFieldsExtension->getFunctions();
        $this->assertCount(2, $functions);
        $this->assertInstanceOf(\Twig_SimpleFunction::class, $functions[0]);
        $this->assertInstanceOf(\Twig_SimpleFunction::class, $functions[1]);
    }
}
