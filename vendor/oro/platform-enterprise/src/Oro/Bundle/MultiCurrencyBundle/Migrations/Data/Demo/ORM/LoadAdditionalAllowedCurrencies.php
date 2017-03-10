<?php

namespace Oro\Bundle\MultiCurrencyBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class LoadAdditionalAllowedCurrencies extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $allowedCurrenciesKey = MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_ALLOWED_CURRENCIES);

        $configManager = $this->container->get('oro_config.global');
        $currencies = $configManager->get($allowedCurrenciesKey);
        $currencies = array_values(array_unique(array_merge($currencies, ['EUR', 'GBP', 'USD', 'UAH'])));
        $configManager->set($allowedCurrenciesKey, $currencies);
        $configManager->flush();
    }
}
