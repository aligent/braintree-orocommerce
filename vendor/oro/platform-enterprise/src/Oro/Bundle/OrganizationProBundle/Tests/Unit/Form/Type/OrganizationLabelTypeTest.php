<?php

namespace Oro\Bundle\OrganizationProBundle\Tests\Unit\Form\Type;

use Oro\Bundle\OrganizationProBundle\Form\Type\OrganizationLabelType;

class OrganizationLabelTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var OrganizationLabelType */
    protected $formType;

    public function setUp()
    {
        $this->formType = new OrganizationLabelType();
    }

    public function testGetParent()
    {
        $this->assertEquals('entity', $this->formType->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_organizationpro_label', $this->formType->getName());
    }
}
