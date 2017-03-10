<?php

namespace Oro\Bundle\SecurityProBundle\Tests\Unit\Tokens;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityProBundle\Tokens\ProUsernamePasswordOrganizationTokenFactory;

class ProUsernamePasswordOrganizationTokenFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $organization = new Organization();
        $factory = new ProUsernamePasswordOrganizationTokenFactory();
        $token = $factory->create('username', 'credentials', 'testProvider', $organization);

        $this->assertInstanceOf('Oro\Bundle\SecurityProBundle\Tokens\ProUsernamePasswordOrganizationToken', $token);
        $this->assertEquals($organization, $token->getOrganizationContext());
        $this->assertEquals('username', $token->getUser());
        $this->assertEquals('credentials', $token->getCredentials());
        $this->assertEquals('testProvider', $token->getProviderKey());
    }
}
