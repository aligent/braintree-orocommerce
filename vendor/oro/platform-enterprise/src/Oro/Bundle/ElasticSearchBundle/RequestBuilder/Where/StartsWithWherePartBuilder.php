<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where;

use Oro\Bundle\SearchBundle\Query\Query;

class StartsWithWherePartBuilder implements WherePartBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function isOperatorSupported($operator)
    {
        return $operator === Query::OPERATOR_STARTS_WITH;
    }

    /**
     * {@inheritdoc}
     */
    public function buildPart($field, $type, $operator, $value)
    {
        return [
            'prefix' => [
                $field => $value
            ]
        ];
    }
}
