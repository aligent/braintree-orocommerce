<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Manager;

use Oro\Bundle\ElasticSearchBundle\RequestBuilder\RequestBuilderRegistry;
use Oro\Bundle\WebsiteElasticSearchBundle\Engine\IndexAgent;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;

class ElasticSearchPartialUpdateManager
{
    /**
     * @var IndexAgent
     */
    private $indexAgent;

    /**
     * @var RequestBuilderRegistry
     */
    private $requestBuilderRegistry;

    /**
     * @param IndexAgent $indexAgent
     * @param RequestBuilderRegistry $requestBuilderRegistry
     */
    public function __construct(IndexAgent $indexAgent, RequestBuilderRegistry $requestBuilderRegistry)
    {
        $this->indexAgent = $indexAgent;
        $this->requestBuilderRegistry = $requestBuilderRegistry;
    }

    /**
     * @param string $visibilityFieldName
     * @param Query $query
     * @param int $visibilityVisible
     */
    public function createCustomerWithoutCustomerGroupVisibility($visibilityFieldName, Query $query, $visibilityVisible)
    {
        $this->indexAgent->ensureIndexExists();
        $items = $this->getSearchResultByQuery($query);
        $request = $this->createBulkRequestForUpdateCustomerVisibility(
            $items,
            $visibilityFieldName,
            $visibilityVisible
        );

        if ($request) {
            $this->indexAgent->getClient()->bulk($request);
            $this->indexAgent->refreshIndex();
        }
    }

    /**
     * @param string $visibilityFieldName
     * @param Query $query
     */
    public function deleteCustomerVisibility($visibilityFieldName, Query $query)
    {
        $this->indexAgent->ensureIndexExists();
        $items = $this->getSearchResultByQuery($query);
        $request = $this->createBulkRequestForDeleteCustomerVisibility($items, $visibilityFieldName);

        if ($request) {
            $this->indexAgent->getClient()->bulk($request);
            $this->indexAgent->refreshIndex();
        }
    }

    /**
     * @param array $productIds
     * @param string $productAlias
     * @param string $visibilityFieldName
     * @param int $visibilityVisible
     */
    public function addCustomerVisibility(array $productIds, $productAlias, $visibilityFieldName, $visibilityVisible)
    {
        $this->indexAgent->ensureIndexExists();
        $items = $this->getItemsForUpdateCustomerVisibilityField($productAlias, $productIds);
        $request = $this->createBulkRequestForUpdateCustomerVisibility(
            $items,
            $visibilityFieldName,
            $visibilityVisible
        );

        if ($request) {
            $this->indexAgent->getClient()->bulk($request);
            $this->indexAgent->refreshIndex();
        }
    }

    /**
     * @param string $productAlias
     * @param array $productIds
     * @return array
     */
    private function getItemsForUpdateCustomerVisibilityField($productAlias, array $productIds)
    {
        $exprBuilder = Criteria::expr();
        $query = new Query();
        $query
            ->from($productAlias)
            ->getCriteria()
            ->andWhere($exprBuilder->in('_id', $productIds));

        return $this->getSearchResultByQuery($query);
    }

    /**
     * @param Query $query
     * @return array
     */
    private function getSearchResultByQuery(Query $query)
    {
        $request['index'] = $this->indexAgent->getIndexName();

        foreach ($this->requestBuilderRegistry->getRequestBuilders() as $requestBuilder) {
            $request = $requestBuilder->build($query, $request);
        }

        $result = $this->indexAgent->getClient()->search($request);

        return $result['hits']['hits'];
    }

    /**
     * @param array $items
     * @param string $visibilityFieldName
     * @return array
     */
    private function createBulkRequestForDeleteCustomerVisibility(array $items, $visibilityFieldName)
    {
        $request = [];
        foreach ($items as $item) {
            $request['body'][] = [
                'index' => [
                    '_index' => $item['_index'],
                    '_type' => $item['_type'],
                    '_id' => $item['_id'],
                ],
            ];

            unset($item['_source'][$visibilityFieldName]);
            $request['body'][] = $item['_source'];
        }

        return $request;
    }

    /**
     * @param array $items
     * @param string $visibilityFieldName
     * @param int $visibilityVisible
     * @return array
     */
    private function createBulkRequestForUpdateCustomerVisibility(
        array $items,
        $visibilityFieldName,
        $visibilityVisible
    ) {
        $request = [];
        foreach ($items as $item) {
            $request['body'][] = [
                'update' => [
                    '_index' => $item['_index'],
                    '_type' => $item['_type'],
                    '_id' => $item['_id'],
                ],
            ];
            $request['body'][] = [
                'doc' => [
                    $visibilityFieldName => $visibilityVisible,
                ],
            ];
        }

        return $request;
    }
}
