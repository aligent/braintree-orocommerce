<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Engine;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Elasticsearch\Client;

use Oro\Bundle\SearchBundle\Provider\AbstractSearchMappingProvider;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\ElasticSearchBundle\RequestBuilder\RequestBuilderRegistry;
use Oro\Bundle\WebsiteElasticSearchBundle\Helper\PlaceholderHelper;
use Oro\Bundle\WebsiteSearchBundle\Engine\Mapper;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\WebsiteSearchBundle\Engine\AbstractEngine;
use Oro\Bundle\WebsiteSearchBundle\Resolver\QueryPlaceholderResolverInterface;

class ElasticSearchEngine extends AbstractEngine
{
    /** @var IndexAgent */
    protected $indexAgent;

    /** @var RequestBuilderRegistry */
    protected $requestBuilderRegistry;

    /** @var Mapper */
    protected $mapper;

    /** @var PlaceholderHelper */
    protected $placeholderHelper;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param QueryPlaceholderResolverInterface $queryPlaceholderResolver
     * @param AbstractSearchMappingProvider $mappingProvider
     * @param IndexAgent $indexAgent
     * @param RequestBuilderRegistry $requestBuilderRegistry
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        QueryPlaceholderResolverInterface $queryPlaceholderResolver,
        AbstractSearchMappingProvider $mappingProvider,
        IndexAgent $indexAgent,
        RequestBuilderRegistry $requestBuilderRegistry
    ) {
        parent::__construct($eventDispatcher, $queryPlaceholderResolver, $mappingProvider);
        $this->indexAgent = $indexAgent;
        $this->requestBuilderRegistry = $requestBuilderRegistry;
    }

    /**
     * @param Mapper $mapper
     */
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param PlaceholderHelper $placeholderHelper
     */
    public function setPlaceholderHelper(PlaceholderHelper $placeholderHelper)
    {
        $this->placeholderHelper = $placeholderHelper;
    }

    /**
     * @return PlaceholderHelper
     * @throws \InvalidArgumentException
     */
    protected function getPlaceholderHelper()
    {
        if (!$this->placeholderHelper) {
            throw new \InvalidArgumentException('Mapping helper is not injected');
        }

        return $this->placeholderHelper;
    }

    /**
     * @return Mapper
     */
    protected function getMapper()
    {
        if (!$this->mapper) {
            throw new \InvalidArgumentException('Mapper is not injected');
        }

        return $this->mapper;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSearch(Query $query, array $context = [])
    {
        $request = [
            'index' => $this->indexAgent->getIndexName()
        ];

        foreach ($this->requestBuilderRegistry->getRequestBuilders() as $requestBuilder) {
            $request = $requestBuilder->build($query, $request);
        }

        $response = $this->getClient()->search($request);

        $results = [];
        if (!empty($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                $item = $this->convertHitToItem($hit, $query);
                if ($item) {
                    $results[] = $item;
                }
            }
        }

        $recordsCount = !empty($response['hits']['total']) ? $response['hits']['total'] : 0;

        return new Result($query, $results, $recordsCount);
    }

    /**
     * @param array $hit
     * @param Query $query
     * @return null|Item
     */
    protected function convertHitToItem(array $hit, Query $query)
    {
        $id = $hit['_id'];
        $fields = empty($hit['fields']) ? [] : $hit['fields'];

        $entityName = $this->getClassByProcessedAlias($hit['_type']);

        return new Item(
            $entityName,
            $id,
            null,
            null,
            $this->getMapper()->mapSelectedData($query, $fields),
            $this->mappingProvider->getEntityConfig($entityName)
        );
    }

    /**
     * @param string $aliasValue
     * @return string
     * @throws \RuntimeException
     */
    private function getClassByProcessedAlias($aliasValue)
    {
        $aliasesByClasses = $this->mappingProvider->getEntityAliases();

        foreach ($aliasesByClasses as $class => $alias) {
            if ($this->getPlaceholderHelper()->isAliasMatch($alias, $aliasValue)) {
                return $class;
            }
        }

        throw new \RuntimeException(sprintf('Couldn\'t get class for processed alias "%s"', $aliasValue));
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return $this->indexAgent->getClient();
    }
}
