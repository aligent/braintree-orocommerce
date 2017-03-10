<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where;

interface WherePartBuilderInterface
{
    /**
     * @param string $operator
     * @return bool
     */
    public function isOperatorSupported($operator);

    /**
     * @param string $field
     * @param string $type
     * @param string $operator
     * @param mixed $value
     * @return array
     */
    public function buildPart($field, $type, $operator, $value);
}
