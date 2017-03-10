<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where\WherePartBuilderRegistry;
use Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where\ElasticExpressionVisitor;

class WhereRequestBuilder implements RequestBuilderInterface
{
    /** @var WherePartBuilderRegistry */
    protected $wherePartBuilderRegistry;

    /**
     * @param WherePartBuilderRegistry $wherePartBuilderRegistry
     */
    public function __construct(WherePartBuilderRegistry $wherePartBuilderRegistry)
    {
        $this->wherePartBuilderRegistry = $wherePartBuilderRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Query $query, array $request)
    {
        $visitor = new ElasticExpressionVisitor($this->wherePartBuilderRegistry->getPartBuilders());

        if ($expression = $query->getCriteria()->getWhereExpression()) {
            $request['body']['query'] = $visitor->dispatch($expression);
        }

        return $request;
    }
}
