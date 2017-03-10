<?php

namespace Oro\Bundle\SecurityProBundle\Tests\Unit\Tokens;

use Oro\Bundle\SecurityProBundle\Tokens\ProOAuthTokenFactory;

class ProOAuthTokenFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $factory = new ProOAuthTokenFactory();
        $token = $factory->create('accessToken');

        $this->assertInstanceOf('Oro\Bundle\SecurityProBundle\Tokens\ProOAuthToken', $token);
        $this->assertEquals('accessToken', $token->getAccessToken());
    }
}
