<?php

namespace Oro\Bundle\MultiCurrencyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfiguration;

class Configuration implements ConfigurationInterface
{
    const SCOPE_NAME_APP = 'app';
    const SCOPE_NAME_ORGANIZATION ='organization';

    const SECOND_DEFAULT_CURRENCY = 'EUR';

    const ROOT_NAME = 'oro_multi_currency';

    const KEY_ALLOWED_CURRENCIES = 'allowed_currencies';
    const KEY_CURRENCY_RATES = 'currency_rates';

    /**
     * Returns full key name by it's last part
     *
     * @param $name string last part of the key name (one of the class cons can be used)
     * @return string full config path key
     */
    public static function getConfigKeyByName($name)
    {
        return self::ROOT_NAME . ConfigManager::SECTION_MODEL_SEPARATOR . $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::ROOT_NAME);

        SettingsBuilder::append(
            $rootNode,
            [
                self::KEY_ALLOWED_CURRENCIES => [
                    'value' => [
                        CurrencyConfiguration::DEFAULT_CURRENCY,
                        self::SECOND_DEFAULT_CURRENCY
                    ],
                    'type' => 'array'
                ],
                self::KEY_CURRENCY_RATES => [
                    'value' => [
                        CurrencyConfiguration::DEFAULT_CURRENCY => ['rateTo' => 1, 'rateFrom' => 1],
                        self::SECOND_DEFAULT_CURRENCY => ['rateTo' => 1, 'rateFrom' => 1]
                    ],
                    'type' => 'array'
                ]
            ]
        );

        return $treeBuilder;
    }
}
