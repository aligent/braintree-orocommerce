<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigKeyByName()
    {
        $this->assertEquals('oro_multi_currency.bar', Configuration::getConfigKeyByName('bar'));
        $this->assertEquals(
            'oro_multi_currency.allowed_currencies',
            Configuration::getConfigKeyByName(Configuration::KEY_ALLOWED_CURRENCIES)
        );
    }
}
