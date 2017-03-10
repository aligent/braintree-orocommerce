<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where;

use Oro\Bundle\SearchBundle\Query\Query;

class RangeWherePartBuilder implements WherePartBuilderInterface
{
    /**
     * @var array
     */
    protected $operatorModifiers = [
        Query::OPERATOR_GREATER_THAN        => 'gt',
        Query::OPERATOR_GREATER_THAN_EQUALS => 'gte',
        Query::OPERATOR_LESS_THAN           => 'lt',
        Query::OPERATOR_LESS_THAN_EQUALS    => 'lte',
    ];

    /**
     * {@inheritdoc}
     */
    public function isOperatorSupported($operator)
    {
        return in_array($operator, [
            Query::OPERATOR_GREATER_THAN,
            Query::OPERATOR_GREATER_THAN_EQUALS,
            Query::OPERATOR_LESS_THAN,
            Query::OPERATOR_LESS_THAN_EQUALS,
        ], true);
    }

    /**
     * {@inheritdoc}
     */
    public function buildPart($field, $type, $operator, $value)
    {
        return  ['range' => [$field => [$this->operatorModifiers[$operator] => $value]]];
    }
}
