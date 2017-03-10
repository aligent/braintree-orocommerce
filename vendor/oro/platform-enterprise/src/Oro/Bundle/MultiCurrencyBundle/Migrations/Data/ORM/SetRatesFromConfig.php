<?php

namespace Oro\Bundle\MultiCurrencyBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\MultiCurrencyBundle\Entity\Rate;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class SetRatesFromConfig extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\MultiCurrencyBundle\Migrations\Data\ORM\SetMultiCurrency'];
    }

    public function load(ObjectManager $manager)
    {
        /**@var ConfigManager **/
        $configManager = $this->container->get('oro_config.global');
        $currencyRates = $configManager->get(
            MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_CURRENCY_RATES)
        );

        foreach ($currencyRates as $currencyCode => $rateValue) {
            $rate = new Rate();
            $rate
                ->setCurrencyCode($currencyCode)
                ->setRateTo($rateValue['rateTo'])
                ->setRateFrom($rateValue['rateFrom'])
                ->setScope(MultiCurrencyConfig::SCOPE_NAME_APP);

            $manager->persist($rate);
        }
        $manager->flush();
    }
}
