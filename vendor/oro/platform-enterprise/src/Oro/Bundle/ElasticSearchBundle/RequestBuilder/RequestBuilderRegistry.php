<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder;

class RequestBuilderRegistry
{
    /** @var RequestBuilderInterface[] */
    protected $requestBuilders = [];

    /**
     * @param RequestBuilderInterface $builder
     */
    public function addRequestBuilder(RequestBuilderInterface $builder)
    {
        if (!in_array($builder, $this->requestBuilders, true)) {
            $this->requestBuilders[] = $builder;
        }
    }

    /**
     * @return RequestBuilderInterface[]
     */
    public function getRequestBuilders()
    {
        return $this->requestBuilders;
    }
}
