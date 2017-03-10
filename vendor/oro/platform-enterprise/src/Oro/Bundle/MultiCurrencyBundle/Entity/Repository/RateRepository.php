<?php

namespace Oro\Bundle\MultiCurrencyBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MultiCurrencyBundle\Entity\Rate;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration;

class RateRepository extends EntityRepository
{
    /**
     * @var null|Organization
     */
    protected $organization = null;

    /**
     * @param string $scope
     * @param int|null $orgId
     *
     * @return bool
     */
    public function removeRatesByScopeAndOrganization($scope, $orgId = null)
    {
        $qb = $this->getRatesQueryBuilderByScopeAndOrganization($scope, $orgId);
        $rateCollection = $qb->getQuery()->getResult();

        /**
         * @var $rate Rate
         */
        foreach ($rateCollection as $rate) {
            $this->_em->remove($rate);
        }
        $this->_em->flush();
        return true;
    }

    /**
     * @return array
     */
    public function getFlatCollectionWithFieldRateFrom()
    {
        $rates = [];
        $ratesCollection =  $this->findAll();

        /**
         * @var Rate rate
         */
        foreach ($ratesCollection as $rate) {
            $organizationId = $rate->getOrganization() instanceof Organization
                ? $rate->getOrganization()->getId()
                : 0;
            if (empty($rates[$organizationId])) {
                $rates[$organizationId] = [];
            }
            $currencyCode = $rate->getCurrencyCode();
            $rates[$organizationId][$currencyCode] = $rate->getRateFrom();
        }

        return $rates;
    }

    /**
     * @param array     $oldState
     * @param array     $newState
     * @param string    $scope
     * @param null|int  $orgId
     *
     * @return bool
     */
    public function applyConfigChanges(array $oldState, array $newState, $scope, $orgId = null)
    {
        $rateCollection = $this
            ->getRatesQueryBuilderByScopeAndOrganization($scope, $orgId)
            ->getQuery()
            ->getResult();

        /**
         * @var $rate Rate
         */
        $availableCurrencyCodes = array_map(function ($rate) {
            return $rate->getCurrencyCode();
        }, $rateCollection);

        $oldStateCurrencyCodes = array_keys($oldState);
        $newStateCurrencyCodes = array_keys($newState);
        $addedCurrencyCodes = array_diff($newStateCurrencyCodes, $oldStateCurrencyCodes);
        $removedCurrencyCodes = array_diff($oldStateCurrencyCodes, $newStateCurrencyCodes);
        $updatedCurrencyCodes = array_filter(
            array_intersect($newStateCurrencyCodes, $oldStateCurrencyCodes),
            function ($currencyCode) use ($oldState, $newState, $availableCurrencyCodes, &$addedCurrencyCodes) {
                $changes = array_diff_assoc(
                    $oldState[$currencyCode],
                    $newState[$currencyCode]
                );

                /**
                 * Check if currency exist in database,
                 * this need in case when we edit 1st time organization
                 */
                if (! in_array($currencyCode, $availableCurrencyCodes)) {
                    array_push($addedCurrencyCodes, $currencyCode);
                }

                return !empty($changes) && in_array($currencyCode, $availableCurrencyCodes);
            }
        );


        foreach ($rateCollection as $rate) {
            $currencyCode = $rate->getCurrencyCode();
            if (in_array($currencyCode, $updatedCurrencyCodes)) {
                $rate->setRateFrom($newState[$currencyCode]['rateFrom']);
                $rate->setRateTo($newState[$currencyCode]['rateTo']);
            } elseif (in_array($currencyCode, $removedCurrencyCodes)) {
                $this->_em->remove($rate);
            }
        }

        $organization = $this->getOrganization($scope, $orgId);
        foreach ($addedCurrencyCodes as $currencyCode) {
            $rate = new Rate();
            $rate
                ->setScope($scope)
                ->setCurrencyCode($currencyCode)
                ->setRateFrom($newState[$currencyCode]['rateFrom'])
                ->setRateTo($newState[$currencyCode]['rateTo']);
            if ($organization instanceof Organization) {
                $rate->setOrganization($organization);
            }
            $this->_em->persist($rate);
        }

        $this->_em->flush();
        return true;
    }

    /**
     * @param string   $scope
     * @param null|int $orgId
     *
     * @return QueryBuilder
     */
    protected function getRatesQueryBuilderByScopeAndOrganization($scope, $orgId = null)
    {
        $qb = $this->createQueryBuilder('rate');
        $this->addWhereByScopeAndOrganization($qb, $scope, $orgId);

        return $qb;
    }

    /**
     * @param string   $scope
     * @param int|null $orgId
     *
     * @return null|Organization
     * @throws \Doctrine\ORM\ORMException
     */
    protected function getOrganization($scope, $orgId = null)
    {
        if (null === $this->organization || $this->organization->getId() !== $orgId) {
            if ($scope === Configuration::SCOPE_NAME_ORGANIZATION) {
                if (!$orgId) {
                    throw new \InvalidArgumentException(
                        'You need to specify organization when editing rates in organization scope'
                    );
                }
                $this->organization = $this->_em->getReference(
                    'OroOrganizationBundle:Organization',
                    $orgId
                );
            } else {
                $this->organization = null;
            }
        }

        return $this->organization;
    }


    /**
     * Add conditions to QB
     *
     * @param QueryBuilder $qb
     * @param string       $scope
     * @param null | null  $orgId
     */
    protected function addWhereByScopeAndOrganization(QueryBuilder $qb, $scope, $orgId = null)
    {
        $qb
            ->where('rate.scope = :scope')
            ->setParameter('scope', $scope, Type::STRING);

        /**
         * @var $organization Organization
         */
        $organization = $this->getOrganization($scope, $orgId);
        if ($organization instanceof Organization) {
            $qb->andWhere('rate.organization = :organization')->setParameter('organization', $organization);
        }
    }
}
