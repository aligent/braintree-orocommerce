<?php
namespace Oro\Bundle\PricingProBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiCurrencyBundle\Provider\ConfigDependencyInterface;
use Oro\Bundle\PricingProBundle\DependencyInjection\Configuration;

class EnabledCurrencyDependencyProvider implements ConfigDependencyInterface
{
    /** @var ConfigManager */
    protected $config;

    /**
     * @inheritDoc
     */
    public function __construct(ConfigManager $globalConfig)
    {
        $this->config = $globalConfig;
    }

    /**
     * @inheritDoc
     */
    public function isValid($enabledCurrencyList, $organization)
    {
        $enabledCurrencies = $this->config->get(
            Configuration::getConfigKeyByName(Configuration::ENABLED_CURRENCIES),
            false,
            true
        );
        if ($this->hasOwnValue($enabledCurrencies)) {
            $stillInUseCurrencyList = array_diff($enabledCurrencies['value'], $enabledCurrencyList);
            if (!empty($stillInUseCurrencyList)) {
                return false;
            }
        }
        $defaultCurrency = $this->config->get(
            Configuration::getConfigKeyByName(Configuration::DEFAULT_CURRENCY),
            false,
            true
        );
        if ($this->hasOwnValue($defaultCurrency) && !in_array($defaultCurrency['value'], $enabledCurrencyList, true)) {
            return false;
        }
        return true;
    }

    /**
     * @param array $configOption
     * @return bool
     */
    protected function hasOwnValue(array $configOption)
    {
        return isset($configOption['use_parent_scope_value']) && !$configOption['use_parent_scope_value'];
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'pricing_enabled_currencies.global';
    }
}
