<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $builder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf(TreeBuilder::class, $builder);
    }

    public function testProcessConfiguration()
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $expected = [
            'settings' => [
                'resolved' => true,
                Configuration::ENABLE_REDIRECT => [
                    'value' => true,
                    'scope' => 'app',
                ],
                Configuration::WEBSITE_COOKIE_NAME => [
                    'value' => 'website',
                    'scope' => 'app',
                ],
                Configuration::WEBSITE_COOKIE_VALUE => [
                    'value' => '',
                    'scope' => 'app',
                ],
                Configuration::MATCHER_ENV_VAR => [
                    'value' => '',
                    'scope' => 'app',
                ],
                Configuration::MATCHER_ENV_VALUE => [
                    'value' => '',
                    'scope' => 'app',
                ],
                Configuration::WEBSITE_MATCHERS_SETTINGS => [
                    'value' => [],
                    'scope' => 'app',
                ],
            ],
        ];
        $this->assertEquals($expected, $processor->processConfiguration($configuration, []));
    }
}
