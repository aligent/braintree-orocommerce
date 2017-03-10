<?php

namespace Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\Method\Config;

use Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\DependencyInjection\Configuration;
use Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\Method\CollectOnDelivery;
use Oro\Bundle\PaymentBundle\DependencyInjection\Configuration as PaymentConfiguration;
use Oro\Bundle\PaymentBundle\Method\Config\AbstractPaymentConfig;
use Oro\Bundle\PaymentBundle\Method\Config\CountryAwarePaymentConfigTrait;

class CollectOnDeliveryConfig extends AbstractPaymentConfig implements CollectOnDeliveryConfigInterface
{
    use CountryAwarePaymentConfigTrait;

    /** {@inheritdoc} */
    protected function getPaymentExtensionAlias()
    {
        return CollectOnDelivery::TYPE;
    }

    /** {@inheritdoc} */
    public function isEnabled()
    {
        return (bool)$this->getConfigValue(Configuration::COLLECT_ON_DELIVERY_ENABLED_KEY);
    }

    /** {@inheritdoc} */
    public function getOrder()
    {
        return (int)$this->getConfigValue(Configuration::COLLECT_ON_DELIVERY_SORT_ORDER_KEY);
    }

    /** {@inheritdoc} */
    public function getLabel()
    {
        return (string)$this->getConfigValue(Configuration::COLLECT_ON_DELIVERY_LABEL_KEY);
    }

    /** {@inheritdoc} */
    public function getShortLabel()
    {
        return (string)$this->getConfigValue(Configuration::COLLECT_ON_DELIVERY_SHORT_LABEL_KEY);
    }

    /** {@inheritdoc} */
    public function isAllCountriesAllowed()
    {
        return $this->getConfigValue(Configuration::COLLECT_ON_DELIVERY_ALLOWED_COUNTRIES_KEY)
            === PaymentConfiguration::ALLOWED_COUNTRIES_ALL;
    }

    /**
     * @inheritDoc
     */
    public function getAllowedCountries()
    {
        return (array)$this->getConfigValue(Configuration::COLLECT_ON_DELIVERY_SELECTED_COUNTRIES_KEY);
    }

    /**
     * @inheritDoc
     */
    public function getAllowedCurrencies()
    {
        return (array)$this->getConfigValue(Configuration::COLLECT_ON_DELIVERY_ALLOWED_CURRENCIES);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAllowedCreditCards()
    {
    	return (array)$this->getConfigValue(Configuration::COLLECT_ON_DELIVERY_PRO_ALLOWED_CC_TYPES_KEY);
    }    
}
