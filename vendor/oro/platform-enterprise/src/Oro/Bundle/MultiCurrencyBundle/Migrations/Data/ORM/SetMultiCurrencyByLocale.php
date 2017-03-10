<?php

namespace Oro\Bundle\MultiCurrencyBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfig;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class SetMultiCurrency extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\CurrencyBundle\Migrations\Data\ORM\SetDefaultCurrencyFromLocale'];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /**@var ConfigManager **/
        $configManager = $this->container->get('oro_config.global');

        $defaultCurrency = $configManager->get(
            CurrencyConfig::getConfigKeyByName(CurrencyConfig::KEY_DEFAULT_CURRENCY)
        );

        $allowedCurrenciesKey = MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_ALLOWED_CURRENCIES);
        $allowedCurrencies = $configManager->get($allowedCurrenciesKey);

        /**
         * We do nothing in case when default currency equals to the system
         */
        if (in_array($defaultCurrency, $allowedCurrencies)) {
            return;
        }

        array_push($allowedCurrencies, $defaultCurrency);

        $configManager->set($allowedCurrenciesKey, $allowedCurrencies);

        $this->setCurrencyRates($defaultCurrency, $configManager);

        $configManager->flush();
    }

    protected function setCurrencyRates($defaultCurrency, $configManager)
    {
        $currencyRatesKey = MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_CURRENCY_RATES);
        $currencyRates = $configManager->get($currencyRatesKey);
        $currencyRates[$defaultCurrency] = [
            'rateTo' => 1,
            'rateFrom' => 1
        ];
        $configManager->set($currencyRatesKey, $currencyRates);
    }
}
