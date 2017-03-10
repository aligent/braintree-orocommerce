<?php

namespace Oro\Bundle\ShippingProBundle\Tests\Behat\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

class MultiplyCurrencyContext extends OroFeatureContext implements KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given There is EUR currency in the system configuration
     */
    public function thereIsEurCurrencyInTheSystemConfiguration()
    {
        $configManager = $this->getContainer()->get('oro_config.global');
        $configManager->set('oro_multi_currency.allowed_currencies', ['EUR', 'USD']);
        $configManager->set('oro_pricing_pro.enabled_currencies', ['EUR', 'USD']);
        $configManager->set('oro_pricing_pro.default_currency', 'EUR');
        $configManager->flush();
    }

    /**
     * @Given Currency is set to USD
     */
    public function currencyIsSetToUsd()
    {
        // this method is empty, because in enterprise version we have multiply currency support
        // and at same time we have to switch currencies in community edition for shipping-rules.feature (2C scenario)
    }

    /**
     * @Given Currency is set to EUR
     */
    public function currencyIsSetToEur()
    {
        // this method is empty, please check currencyIsSetToUsd() function.
    }
}
