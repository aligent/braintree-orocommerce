<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where;

use Oro\Bundle\SearchBundle\Query\Query;

class EqualsWherePartBuilder implements WherePartBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function isOperatorSupported($operator)
    {
        return in_array($operator, [Query::OPERATOR_EQUALS, Query::OPERATOR_NOT_EQUALS], true);
    }

    /**
     * {@inheritdoc}
     */
    public function buildPart($field, $type, $operator, $value)
    {
        $condition = ['match' => [$field => $value]];

        if ($operator === Query::OPERATOR_NOT_EQUALS) {
            return ['bool' => ['must_not' => $condition]];
        }

        return $condition;
    }
}
