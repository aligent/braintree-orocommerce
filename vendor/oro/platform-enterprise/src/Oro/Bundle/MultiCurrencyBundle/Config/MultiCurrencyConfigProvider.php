<?php

namespace Oro\Bundle\MultiCurrencyBundle\Config;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CurrencyBundle\Config\DefaultCurrencyConfigProvider;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class MultiCurrencyConfigProvider extends DefaultCurrencyConfigProvider
{
    /**
     * @var ConfigManager
     */
    protected $systemConfigManager;

    /**
     * MultiCurrencyConfigManager constructor.
     *
     * @param ConfigManager $configManager organization config manager
     * @param ConfigManager $systemConfigManager system config manager
     */
    public function __construct(ConfigManager $configManager, ConfigManager $systemConfigManager)
    {
        $this->systemConfigManager = $systemConfigManager;
        parent::__construct($configManager);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyList()
    {
        $systemCurrencyList = $this->systemConfigManager->get(
            MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_ALLOWED_CURRENCIES)
        );
        $organizationCurrencyList = $this->configManager->get(
            MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_ALLOWED_CURRENCIES)
        );

        return array_intersect(
            $organizationCurrencyList,
            $systemCurrencyList
        );
    }
}
