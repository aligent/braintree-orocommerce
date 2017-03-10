<?php

namespace Oro\Bundle\MultiCurrencyBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\MultiCurrencyBundle\Entity\Rate;
use Oro\Bundle\MultiCurrencyBundle\Provider\RateProvider;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class LoadRateDemoData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @var array
     */
    protected $rates = [
        ['currency_code' => 'USD', 'rate_from' => 1, 'rate_to' => 1],
        ['currency_code' => 'GBP', 'rate_from' => 1.22, 'rate_to' => 0.81],
        ['currency_code' => 'EUR', 'rate_from' => 1.09, 'rate_to' => 0.91],
        ['currency_code' => 'UAH', 'rate_from' => 0.039, 'rate_to' => 25.63]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData',
            'Oro\Bundle\MultiCurrencyBundle\Migrations\Data\Demo\ORM\LoadAdditionalAllowedCurrencies'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /* @var $repository EntityRepository */
        $repository = $manager->getRepository('OroMultiCurrencyBundle:Rate');

        foreach ($this->rates as $item) {
            $rate = $repository->findOneBy([
                'currencyCode' => $item['currency_code'],
                'scope' => MultiCurrencyConfig::SCOPE_NAME_APP
            ]);

            if ($rate instanceof Rate) {
                $rate
                    ->setRateTo($item['rate_to'])
                    ->setRateFrom($item['rate_from']);
            } else {
                $rate = new Rate();
                $rate
                    ->setCurrencyCode($item['currency_code'])
                    ->setRateTo($item['rate_to'])
                    ->setRateFrom($item['rate_from'])
                    ->setScope(MultiCurrencyConfig::SCOPE_NAME_APP);

                $manager->persist($rate);
            }
        }
        $manager->flush();

        $configManager = $this->container->get('oro_config.global');
        $currencyRatesKey = MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_CURRENCY_RATES);
        $currencyRates = $configManager->get($currencyRatesKey);
        $configManager->set(
            $currencyRatesKey,
            array_merge(
                $currencyRates,
                [
                    'USD' => ['rateFrom' => 1, 'rateTo' => 1],
                    'GBP' => ['rateFrom' => 1.22, 'rateTo' => 0.81],
                    'EUR' => ['rateFrom' => 1.09, 'rateTo' => 0.91],
                    'UAH' => ['rateFrom' => 0.039, 'rateTo' => 25.63],
                ]
            )
        );
        $configManager->flush();

        /**
         * @var RateProvider $rateProvider
         */
        $rateProvider = $this->container->get('oro_multi_currency.provider.rate');
        $rateProvider->clearCache();
    }
}
