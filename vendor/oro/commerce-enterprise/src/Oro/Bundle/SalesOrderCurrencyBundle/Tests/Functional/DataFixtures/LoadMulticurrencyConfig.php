<?php

namespace Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\MultiCurrencyBundle\Entity\Rate;
use Oro\Bundle\MultiCurrencyBundle\Provider\RateProvider;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class LoadMulticurrencyConfig extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const RATE_FROM_EUR = 2;

    protected $organizationRates = [
        'USD' => [
            'rate_from' => 1,
            'rate_to'   => 1
        ],
        'EUR' => [
            'rate_from' => self::RATE_FROM_EUR,
            'rate_to'   => 0.5
        ]
    ];

    /**
     * @param EntityManager $manager
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        /**
         * @var $rate Rate
         */
        $rates = $manager
            ->getRepository('OroMultiCurrencyBundle:Rate')
            ->findBy(['organization' => $organization]);

        foreach ($rates as $rate) {
            if (isset($this->organizationRates[$rate->getCurrencyCode()])) {
                $rateValues = $this->organizationRates[$rate->getCurrencyCode()];
                foreach ($rateValues as $rateKey => $rateValue) {
                    $propertyAccessor->setValue($rate, $rateKey, $rateValue);
                }
                unset($this->organizationRates[$rate->getCurrencyCode()]);
            }
        }

        foreach ($this->organizationRates as $currencyCode => $rateValues) {
            $rate = new Rate();
            $rate
                ->setOrganization($organization)
                ->setScope(MultiCurrencyConfig::SCOPE_NAME_ORGANIZATION)
                ->setCurrencyCode($currencyCode);

            foreach ($rateValues as $rateKey => $rateValue) {
                $propertyAccessor->setValue($rate, $rateKey, $rateValue);
            }

            $manager->persist($rate);
        }

        $rate->setRateFrom(self::RATE_FROM_EUR);
        $manager->flush();

        /**
         * @var $rateProvider RateProvider
         */
        $rateProvider = $this->container->get('oro_multi_currency.provider.rate');
        $rateProvider->clearCache();
    }
}
