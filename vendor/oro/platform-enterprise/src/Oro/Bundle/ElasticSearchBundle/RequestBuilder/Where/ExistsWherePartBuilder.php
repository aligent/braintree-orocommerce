<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where;

use Oro\Bundle\SearchBundle\Query\Query;

class ExistsWherePartBuilder implements WherePartBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function isOperatorSupported($operator)
    {
        return in_array($operator, [Query::OPERATOR_EXISTS, Query::OPERATOR_NOT_EXISTS], true);
    }

    /**
     * {@inheritdoc}
     */
    public function buildPart($field, $type, $operator, $value)
    {
        switch ($operator) {
            case Query::OPERATOR_EXISTS:
                $operator = 'must';
                break;
            case Query::OPERATOR_NOT_EXISTS:
                $operator = 'must_not';
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Unsupported operator "%s"', $operator)
                );
        }

        return [
            'bool' => [
                $operator => [
                    'exists' => [
                        'field' => $field
                    ]
                ]
            ]
        ];
    }
}
