<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\ElasticSearchBundle\Engine\IndexAgent;

class ContainsWherePartBuilder implements WherePartBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function isOperatorSupported($operator)
    {
        return in_array($operator, [Query::OPERATOR_CONTAINS, Query::OPERATOR_NOT_CONTAINS], true);
    }

    /**
     * {@inheritdoc}
     */
    public function buildPart($field, $type, $operator, $value)
    {
        $condition = ['match' => [sprintf('%s.%s', $field, IndexAgent::FULLTEXT_ANALYZED_FIELD) => $value]];

        if ($operator === Query::OPERATOR_NOT_CONTAINS) {
            return ['bool' => ['must_not' => $condition]];
        }

        return $condition;
    }
}
