<?php

namespace Oro\Bundle\PricingProBundle\Manager;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CurrencyBundle\Provider\CurrencyProviderInterface;
use Oro\Bundle\PricingProBundle\DependencyInjection\Configuration;

class DisplayedCurrenciesConfigProvider implements CurrencyProviderInterface
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyList()
    {
        return $this->configManager->get(Configuration::getConfigKeyByName(Configuration::ENABLED_CURRENCIES));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultCurrency()
    {
        return $this->configManager->get(Configuration::getConfigKeyByName(Configuration::DEFAULT_CURRENCY));
    }
}
