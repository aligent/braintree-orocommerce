<?php

namespace Oro\Bundle\MultiWebsiteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
    const ENABLE_REDIRECT = 'enable_redirect';
    const WEBSITE_COOKIE_NAME = 'website_cookie_name';
    const WEBSITE_COOKIE_VALUE = 'website_cookie_value';
    const WEBSITE_MATCHERS_SETTINGS = 'website_matchers_settings';
    const MATCHER_ENV_VAR = 'matcher_env_var';
    const MATCHER_ENV_VALUE = 'matcher_env_value';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(OroMultiWebsiteExtension::ALIAS);

        SettingsBuilder::append(
            $rootNode,
            [
                self::ENABLE_REDIRECT => ['type' => 'boolean', 'value' => true],
                self::WEBSITE_COOKIE_NAME => ['type' => 'text', 'value' => 'website'],
                self::WEBSITE_COOKIE_VALUE => ['type' => 'text', 'value' => ''],
                self::MATCHER_ENV_VAR => ['type' => 'text', 'value' => ''],
                self::MATCHER_ENV_VALUE => ['type' => 'text', 'value' => ''],
                self::WEBSITE_MATCHERS_SETTINGS => ['type' => 'array', 'value' => []],
            ]
        );

        return $treeBuilder;
    }
}
