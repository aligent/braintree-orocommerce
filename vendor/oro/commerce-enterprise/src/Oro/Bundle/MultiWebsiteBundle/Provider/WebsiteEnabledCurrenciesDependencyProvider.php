<?php

namespace Oro\Bundle\MultiWebsiteBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiCurrencyBundle\Provider\ConfigDependencyInterface;
use Oro\Bundle\PricingProBundle\DependencyInjection\Configuration;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class WebsiteEnabledCurrenciesDependencyProvider implements ConfigDependencyInterface
{
    /** @var ConfigManager */
    protected $config;

    /** @var WebsiteRepository */
    protected $websiteManager;

    /**
     * @inheritDoc
     */
    public function __construct(ConfigManager $websiteConfig, ManagerRegistry $registry)
    {
        $this->config = $websiteConfig;
        $this->websiteManager = $registry->getRepository(Website::class);
    }

    /**
     * @inheritDoc
     */
    public function isValid($enabledCurrencyList, $organization)
    {
        $isValid = true;
        $websiteSelectCriteria = [];
        if (null !== $organization) {
            $websiteSelectCriteria['organization'] = $organization;
        }
        $affectedWebsiteCollection = $this->websiteManager->findBy($websiteSelectCriteria);

        /** @var Website $website */
        foreach ($affectedWebsiteCollection as $website) {
            $websiteEnabledCurrencies = $this->config->get(
                Configuration::getConfigKeyByName(Configuration::ENABLED_CURRENCIES),
                false,
                true,
                $website->getId()
            );

            if ($this->hasOwnConfig($websiteEnabledCurrencies)) {
                $stillInUseCurrencyList = array_diff($websiteEnabledCurrencies['value'], $enabledCurrencyList);
                $isValid &= empty($stillInUseCurrencyList);
            }

            $defaultCurrency = $this->config->get(
                Configuration::getConfigKeyByName(Configuration::DEFAULT_CURRENCY),
                false,
                true,
                $website->getId()
            );
            if ($this->hasOwnConfig($defaultCurrency)) {
                $isValid &= in_array($defaultCurrency['value'], $enabledCurrencyList, true);
            }
        }

        return $isValid;
    }

    /**
     * @param array $configOption
     * @return bool
     */
    protected function hasOwnConfig(array $configOption)
    {
        return isset($configOption['use_parent_scope_value']) && !$configOption['use_parent_scope_value'];
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'pricing_enabled_currencies.website';
    }
}
