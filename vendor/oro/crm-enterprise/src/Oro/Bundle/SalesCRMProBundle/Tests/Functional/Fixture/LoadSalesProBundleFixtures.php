<?php

namespace Oro\Bundle\SalesCRMProBundle\Tests\Functional\Fixture;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MultiCurrencyBundle\Entity\Rate;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;

class LoadSalesProBundleFixtures extends LoadSalesBundleFixtures
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        parent::load($manager);
        $this->addRates();
    }

    protected function addRates()
    {
        $rates = [
            ['currency_code' => 'USD', 'rate_from' => 1, 'rate_to' => 1],
            ['currency_code' => 'GBP', 'rate_from' => 1.22, 'rate_to' => 0.81],
            ['currency_code' => 'EUR', 'rate_from' => 1.09, 'rate_to' => 0.91],
            ['currency_code' => 'UAH', 'rate_from' => 0.039, 'rate_to' => 25.63]
        ];

        foreach ($rates as $item) {
            $rate = new Rate();
            $rate->setCurrencyCode($item['currency_code']);
            $rate->setRateFrom($item['rate_from']);
            $rate->setRateTo($item['rate_to']);
            $rate->setScope(MultiCurrencyConfig::SCOPE_NAME_APP);
            $this->em->persist($rate);
        }

        $this->em->flush();
    }
}
