<?php

namespace Oro\Bundle\PricingProBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const ENABLED_CURRENCIES = 'enabled_currencies';
    const DEFAULT_CURRENCY = 'default_currency';

    /**
     * @var string
     */
    protected static $configKeyToPriceList;

    /**
     * @var string
     */
    protected static $configKeyToFullPriceList;

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root(OroPricingProExtension::ALIAS);

        SettingsBuilder::append(
            $rootNode,
            [
                self::ENABLED_CURRENCIES => ['value' => [CurrencyConfiguration::DEFAULT_CURRENCY], 'type' => 'array'],
                self::DEFAULT_CURRENCY => ['value' => CurrencyConfiguration::DEFAULT_CURRENCY]
            ]
        );

        return $treeBuilder;
    }

    /**
     * @param string $key
     * @return string
     */
    public static function getConfigKeyByName($key)
    {
        return implode(ConfigManager::SECTION_MODEL_SEPARATOR, [OroPricingProExtension::ALIAS, $key]);
    }
}
