<?php

namespace Oro\Bundle\ElasticSearchBundle\Engine;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Elasticsearch\Client;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Engine\AbstractEngine;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\ElasticSearchBundle\RequestBuilder\RequestBuilderRegistry;

class ElasticSearch extends AbstractEngine
{
    const ENGINE_NAME = 'elastic_search';

    /** @var ObjectMapper */
    protected $mapper;

    /** @var IndexAgent */
    protected $indexAgent;

    /** @var RequestBuilderRegistry */
    protected $requestBuilderRegistry;

    /**
     * @param ManagerRegistry $registry
     * @param EventDispatcherInterface $eventDispatcher
     * @param ObjectMapper $mapper
     * @param IndexAgent $indexAgent
     * @param RequestBuilderRegistry $requestBuilderRegistry
     */
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $eventDispatcher,
        ObjectMapper $mapper,
        IndexAgent $indexAgent,
        RequestBuilderRegistry $requestBuilderRegistry
    ) {
        parent::__construct($registry, $eventDispatcher);

        $this->mapper = $mapper;
        $this->indexAgent = $indexAgent;
        $this->requestBuilderRegistry = $requestBuilderRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSearch(Query $query)
    {
        $request = ['index' => $this->indexAgent->getIndexName()];

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

        return ['results' => $results, 'records_count' => $recordsCount];
    }

    /**
     * @param array $hit
     * @param Query $query
     * @return null|Item
     */
    protected function convertHitToItem(array $hit, Query $query)
    {
        $type = null;
        if (!empty($hit['_type'])) {
            $type = $hit['_type'];
        }

        $id = null;
        if (!empty($hit['_id'])) {
            $id = $hit['_id'];
        }

        if (!$type || !$id) {
            return null;
        }

        $entityName = $this->getEntityName($type);
        if (!$entityName) {
            return null;
        }

        return new Item(
            $entityName,
            $id,
            null,
            null,
            $this->mapper->mapSelectedData($query, isset($hit['fields']) ? $hit['fields'] : []),
            $this->mapper->getEntityConfig($entityName)
        );
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return $this->indexAgent->getClient();
    }

    /**
     * @param string $alias
     * @return string|null
     */
    protected function getEntityName($alias)
    {
        $aliasesToEntities = array_flip($this->mapper->getEntitiesListAliases());

        return !empty($aliasesToEntities[$alias]) ? $aliasesToEntities[$alias] : null;
    }
}
