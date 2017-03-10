<?php

namespace Oro\Bundle\SecurityProBundle\Tests\Unit\Tokens;

use Oro\Bundle\SecurityProBundle\Tokens\ProWsseTokenFactory;

class ProWsseTokenFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $factory = new ProWsseTokenFactory();
        $token = $factory->create();

        $this->assertInstanceOf('Oro\Bundle\SecurityProBundle\Tokens\ProWsseToken', $token);
    }
}
