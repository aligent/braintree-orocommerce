<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder;

use Oro\Bundle\SearchBundle\Query\Query;

class LimitRequestBuilder implements RequestBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(Query $query, array $request)
    {
        $from = $query->getCriteria()->getFirstResult();

        if (null !== $from) {
            $request['body']['from'] = (int)$from;
        }

        $size = $query->getCriteria()->getMaxResults();
        if (null !== $size && $size) {
            $size = (int)$size;
            // manual reducing of window size
            if ($from + $size > Query::INFINITY) {
                $size = Query::INFINITY - $from;
            }
            $request['body']['size'] = $size;
        }

        return $request;
    }
}
