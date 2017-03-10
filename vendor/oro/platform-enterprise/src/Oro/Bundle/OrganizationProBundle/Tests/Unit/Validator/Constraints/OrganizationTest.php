<?php

namespace Oro\Bundle\OrganizationProBundle\Tests\Unit\Validator\Constrains;

use Oro\Bundle\OrganizationProBundle\Validator\Constraints\Organization;

class OrganizationTest extends \PHPUnit_Framework_TestCase
{
    /** @var Organization */
    protected $organization;

    protected function setUp()
    {
        $this->organization = new Organization();
    }

    public function testMessage()
    {
        $this->assertEquals(
            'You have no access to set this value as {{ organization }}.',
            $this->organization->message
        );
    }

    public function testValidatedBy()
    {
        $this->assertEquals('organization_validator', $this->organization->validatedBy());
    }

    public function testGetTargets()
    {
        $this->assertEquals('class', $this->organization->getTargets());
    }
}
