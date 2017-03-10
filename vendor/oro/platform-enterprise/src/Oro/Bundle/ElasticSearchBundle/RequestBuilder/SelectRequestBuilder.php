<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder;

use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;

class SelectRequestBuilder implements RequestBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(Query $query, array $request)
    {
        $fields = $query->getSelect();

        if (empty($fields)) {
            $request['fields'] = null;
        } else {
            $result = [];
            foreach ($fields as $field) {
                list($type, $name) = Criteria::explodeFieldTypeName($field);
                $result[] = $name;
            }
            $request['fields'] = implode(',', $result);
        }

        return $request;
    }
}
