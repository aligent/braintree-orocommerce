<?php

namespace Oro\Bundle\MultiCurrencyBundle\Query;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\MultiCurrencyBundle\Provider\RateProvider;

class CurrencyQueryBuilderTransformer implements CurrencyQueryBuilderTransformerInterface
{
    /** @var RateProvider  */
    protected $rateProvider;

    public function __construct(RateProvider $rateProvider)
    {
        $this->rateProvider  = $rateProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformSelectQuery(
        $originalFieldName,
        QueryBuilder $qb = null,
        $rootAlias = null,
        $newFieldName = ''
    ) {
        if (!$originalFieldName) {
            throw new \InvalidArgumentException('You must specify original field name for base currency query');
        }

        if (!$qb && !$rootAlias) {
            throw new \InvalidArgumentException('You must specify query builder or rootAlias for base currency query');
        }

        if (!$rootAlias) {
            $rootAliases = $qb->getRootAliases();
            $rootAlias = array_shift($rootAliases);
        }

        $query = sprintf(
            'COALESCE(%1$s.base%2$sValue, %3$s)',
            $rootAlias,
            ucfirst($originalFieldName),
            $this->prepareRateCondition($rootAlias, $originalFieldName)
        );

        if ($newFieldName) {
            $query .= sprintf(' as %s', $newFieldName);
        }

        return $query;
    }

    /**
     * @param $rootAlias
     * @param $originalFieldName
     *
     * @return string
     */
    protected function prepareRateCondition($rootAlias, $originalFieldName)
    {
        $rates = $this->rateProvider->getCurrentOrganizationRateList();
        if (empty($rates)) {
            return sprintf(
                '%1$s.%2$sValue',
                $rootAlias,
                $originalFieldName
            );
        }

        $query = '';
        foreach ($rates as $currencyCode => $rate) {
            $query .= sprintf(
                'WHEN \'%3$s\' THEN %4$s * %1$s.%2$sValue ',
                $rootAlias,
                $originalFieldName,
                $currencyCode,
                $rate
            );
        }

        return sprintf(
            'COALESCE(CASE %1$s.%2$sCurrency %3$s ELSE %1$s.%2$sValue END)',
            $rootAlias,
            $originalFieldName,
            $query
        );
    }
}
