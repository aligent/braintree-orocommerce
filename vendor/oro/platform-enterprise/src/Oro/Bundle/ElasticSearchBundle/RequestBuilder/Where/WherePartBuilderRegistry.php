<?php

namespace Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where;

class WherePartBuilderRegistry
{
    /** @var WherePartBuilderInterface[] */
    protected $partBuilders = [];

    /**
     * @param WherePartBuilderInterface $builder
     */
    public function addWherePartBuilder(WherePartBuilderInterface $builder)
    {
        if (!in_array($builder, $this->partBuilders, true)) {
            $this->partBuilders[] = $builder;
        }
    }

    /**
     * @return WherePartBuilderInterface[]
     */
    public function getPartBuilders()
    {
        return $this->partBuilders;
    }
}
