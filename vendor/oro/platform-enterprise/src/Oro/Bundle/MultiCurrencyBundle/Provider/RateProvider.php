<?php

namespace Oro\Bundle\MultiCurrencyBundle\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

use Oro\Component\DependencyInjection\ServiceLink;
use Oro\Bundle\MultiCurrencyBundle\Entity\Rate;
use Oro\Bundle\MultiCurrencyBundle\Exception\RateNotFoundException;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class RateProvider
{
    const RATE_CACHE_KEY    = 'oro_multi_currency_rates';

    /**
     * @var ServiceLink
     */
    protected $entityManagerLink;

    /**
     * @var array
     */
    protected $rates;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var ServiceLink
     */
    protected $securityFacadeLink;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ServiceLink     $entityManagerLink
     * @param ServiceLink     $securityFacadeLink
     * @param CacheProvider   $cache
     * @param LoggerInterface $logger
     */
    public function __construct(
        ServiceLink     $entityManagerLink,
        ServiceLink     $securityFacadeLink,
        CacheProvider   $cache,
        LoggerInterface $logger
    ) {
        $this->entityManagerLink  = $entityManagerLink;
        $this->securityFacadeLink = $securityFacadeLink;
        $this->cache              = $cache;
        $this->logger             = $logger;
    }

    /**
     * @param string $fromCurrency
     *
     * @return string
     */
    public function getRate($fromCurrency)
    {
        $defaultRate = 1;
        $rates = $this->getCurrentOrganizationRateList();
        if (!isset($rates[$fromCurrency])) {
            $this->logger->alert(
                sprintf('Can\'t get exchange rate for currency "%s".', $fromCurrency)
            );
            return $defaultRate;
        }

        return $rates[$fromCurrency];
    }

    /**
     * Get currency list rates
     *
     * @return string[]
     *
     * @throws RateNotFoundException
     */
    public function getCurrentOrganizationRateList()
    {
        $rates = $this->getRateList();

        /**
         * @var SecurityFacade $securityFacade
         */
        $securityFacade = $this->securityFacadeLink->getService();
        $currentOrganization = $securityFacade->getOrganization();

        if ($currentOrganization instanceof Organization) {
            /**
             * Return system rates in case when:
             * 1. User enters to the system from global organization
             * 2. When no records exist about current organization in the rates table
             */
            if ($currentOrganization->getIsGlobal() || empty($this->rates[$currentOrganization->getId()])) {
                return $rates[0];
            }

            return $rates[$currentOrganization->getId()];
        }

        return [];
    }

    public function getRateList()
    {
        if (null === $this->rates) {
            $this->rates = $this->getRateFromCache();
        }

        return $this->rates;
    }

    protected function getRateFromCache()
    {
        $rates = $this->cache->fetch(self::RATE_CACHE_KEY);
        if (!$rates) {
            $rates = $this->getRateCollectionFromDb();
            $this->cache->save(self::RATE_CACHE_KEY, $rates);
        }

        return $rates;
    }

    /**
     * @return array
     */
    protected function getRateCollectionFromDb()
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $this->entityManagerLink->getService();
        $rates = $entityManager
            ->getRepository('OroMultiCurrencyBundle:Rate')
            ->getFlatCollectionWithFieldRateFrom();

        if (empty($rates[0])) {
            /**
             * Current flow doesn't allow user to remove all rates.
             * Only exceptional system state can cause issue with "Missed system rates".
             */
            $rates[0] = [];
            $this->logger->alert("Can't find system currency exchange rates.");
        }

        return $rates;
    }

    /**
     * Clear All cache data
     */
    public function clearCache()
    {
        $this->cache->delete(self::RATE_CACHE_KEY);
        $this->rates = null;
    }
}
