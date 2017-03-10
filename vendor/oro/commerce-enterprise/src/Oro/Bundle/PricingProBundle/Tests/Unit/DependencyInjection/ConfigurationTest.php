<?php

namespace Oro\Bundle\PricingProBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\PricingProBundle\DependencyInjection\Configuration;
use Oro\Bundle\PricingProBundle\DependencyInjection\OroPricingProExtension;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $treeBuilder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
    }

    public function testProcessConfiguration()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'settings' => [
                'resolved' => true,
                'enabled_currencies' => [
                    'value' => ['USD'],
                    'scope' => 'app'
                ],
                'default_currency' => [
                    'value' => 'USD',
                    'scope' => 'app'
                ]
            ]
        ];

        $this->assertEquals($expected, $processor->processConfiguration($configuration, []));
    }

    public function testGetConfigKeyByName()
    {
        $key = 'options';
        $configKey = Configuration::getConfigKeyByName($key);
        $expectedKey = OroPricingProExtension::ALIAS.ConfigManager::SECTION_MODEL_SEPARATOR.$key;
        static::assertEquals($expectedKey, $configKey);
    }
}
