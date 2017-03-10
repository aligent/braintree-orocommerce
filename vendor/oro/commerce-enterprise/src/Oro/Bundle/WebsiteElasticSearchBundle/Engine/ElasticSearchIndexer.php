<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Engine;

use Psr\Log\LoggerAwareTrait;

use Oro\Bundle\WebsiteElasticSearchBundle\Helper\PlaceholderHelper;
use Oro\Bundle\WebsiteSearchBundle\Engine\AbstractIndexer;

class ElasticSearchIndexer extends AbstractIndexer
{
    use LoggerAwareTrait;

    /** @var IndexAgent */
    private $indexAgent;

    /** @var PlaceholderHelper */
    private $placeholderHelper;

    /**
     * @param IndexAgent $indexAgent
     */
    public function setIndexAgent(IndexAgent $indexAgent)
    {
        $this->indexAgent = $indexAgent;
    }

    /**
     * @return IndexAgent
     */
    public function getIndexAgent()
    {
        if (!$this->indexAgent) {
            throw new \RuntimeException('IndexAgent is not set.');
        }

        return $this->indexAgent;
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
     */
    protected function getPlaceholderHelper()
    {
        if (!$this->placeholderHelper) {
            throw new \RuntimeException('PlaceholderHelper is not set.');
        }

        return $this->placeholderHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function saveIndexData($entityClass, array $entitiesData, $entityAliasTemp, array $context)
    {
        $realAlias = $this->getEntityAlias($entityClass, $context);

        if (null === $realAlias || empty($entitiesData)) {
            return 0;
        }

        $this->processData($entityClass, $realAlias, $entityAliasTemp, $entitiesData);

        return count($entitiesData);
    }

    /**
     * {@inheritdoc}
     */
    protected function renameIndex($temporaryAlias, $currentAlias)
    {
        $parameters = [
            'index' => $this->getIndexAgent()->getIndexName(),
            'type' => $currentAlias
        ];

        $parameters['body'] = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        [
                            'match' => [
                                IndexAgent::TMP_ALIAS_FIELD => $temporaryAlias
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->getIndexAgent()->getClient()->deleteByQuery($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity, array $context = [])
    {
        if (!$this->getIndexAgent()->isIndexExists($this->getIndexAgent()->getIndexName())) {
            return true;
        }
        //Put ensureIndexExists method if you want always make delete query;

        $entities = is_array($entity) ? $entity : [$entity];

        $parameters = ['index' => $this->getIndexAgent()->getIndexName()];

        $entityIdsByClass = [];
        foreach ($entities as $entityData) {
            $entityClass = $this->doctrineHelper->getEntityClass($entityData);
            $entityId = $this->doctrineHelper->getSingleEntityIdentifier($entityData);

            if (!$this->mappingProvider->isClassSupported($entityClass)) {
                continue;
            }

            $entityIdsByClass[$entityClass][] = $entityId;
        }

        $isSuccessful = true;
        foreach ($entityIdsByClass as $entityClass => $entityIds) {
            $type = $this->getEntityAlias($entityClass, $context);

            if ($type) {
                $parameters['type'][] = $type;
            } else {
                $parameters['type'] = $this->getEntityTypesByClass($entityClass);
            }

            $parameters['body']['query']['bool']['should'][] = $this->prepareBodyForDelete($entityIds);
            $response = $this->getIndexAgent()->getClient()->deleteByQuery($parameters);

            $isSuccessful &= !empty($response['failures']);
        }

        return $isSuccessful;
    }

    /**
     * {@inheritdoc}
     */
    public function reindex($classOrClasses = null, array $context = [])
    {
        $this->indexAgent->ensureIndexExists();

        $handledItems = parent::reindex($classOrClasses, $context);

        // Refresh search index after reindex
        $this->getIndexAgent()->refreshIndex();

        return $handledItems;
    }

    /**
     * {@inheritdoc}
     */
    public function resetIndex($class = null, array $context = [])
    {
        $indexName = $this->indexAgent->getIndexName();
        $this->indexAgent->ensureIndexExists();

        if (null === $class) {
            if (empty($context[self::CONTEXT_CURRENT_WEBSITE_ID_KEY])) {
                $this->indexAgent->recreateIndex();

                return;
            }

            $class = $this->mappingProvider->getEntityClasses();
        }

        $classes = is_array($class) ? $class : [$class];

        $parameters = ['index' => $indexName, 'type' => []];

        foreach ($classes as $className) {
            $type = $this->getEntityAlias($className, $context);

            if ($type) {
                $parameters['type'][] = $type;
            } else {
                $parameters['type'] = array_merge($parameters['type'], $this->getEntityTypesByClass($className));
            }
        }

        // delete by query API was added by plugin
        if (!empty($parameters['type'])) {
            $parameters['body'] = ['query' => ['match_all' => []]];
            $this->getIndexAgent()->getClient()->deleteByQuery($parameters);
        }
    }

    /**
     * @param string $className
     * @return array
     */
    private function getEntityTypesByClass($className)
    {
        $types = [];
        $indexName = $this->getIndexAgent()->getIndexName();
        $mappings = $this->getIndexAgent()->getClient()->indices()->getMapping(['index' => $indexName]);
        foreach ($mappings[$indexName]['mappings'] as $type => $mapping) {
            if ($this->isTypeOfEntityClass($type, $className)) {
                $types[] = $type;
            }
        }

        return $types;
    }

    /**
     * @param $type
     * @param $className
     * @return bool
     */
    private function isTypeOfEntityClass($type, $className)
    {
        $entityAlias = $this->mappingProvider->getEntityAlias($className);

        return $this->getPlaceholderHelper()->isAliasMatch($entityAlias, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function reindexEntityClass($entityClass, array $context)
    {
        // TODO: Do we need to check mapping before recreate it? We discuss it in BB-5393
        $this->getIndexAgent()->createMappings($entityClass, $context);

        return parent::reindexEntityClass($entityClass, $context);
    }

    /**
     * @param string $entityClass
     * @param string $realAlias
     * @param string $entityAliasTemp
     * @param array $entitiesData
     */
    private function processData($entityClass, $realAlias, $entityAliasTemp, array $entitiesData)
    {
        $body = [];

        foreach ($entitiesData as $entityId => $entityData) {
            $indexIdentifier = ['_type' => $realAlias, '_id' => $entityId];
            $body[] = ['delete' => $indexIdentifier];

            $indexData = $this->prepareIndexData($entityData);
            if ($indexData) {
                $indexData[IndexAgent::TMP_ALIAS_FIELD] = $entityAliasTemp;

                $body[] = ['create' => $indexIdentifier];
                $body[] = $indexData;
            }
        }

        $response = $this->getIndexAgent()->getClient()->bulk(
            [
                'index' => $this->getIndexAgent()->getIndexName(),
                'body' => $body
            ]
        );

        if ($response['errors']) {
            $this->logErrors($response);
            throw new \RuntimeException('Reindex failed');
        }
    }

    /**
     * @param array $indexData
     * @return array
     */
    private function prepareIndexData(array $indexData)
    {
        $data = call_user_func_array('array_merge', $indexData);

        foreach ((array)$data as $dataFieldName => $dataValue) {
            if ($dataValue instanceof \DateTime) {
                $dataValue->setTimezone(new \DateTimeZone('UTC'));
                $data[$dataFieldName] = $dataValue->format('Y-m-d H:i:s');
            }
        }

        return $data;
    }

    /**
     * @param array $entityIds
     * @return array
     */
    private function prepareBodyForDelete($entityIds)
    {
        return [
            'bool' => [
                'must' => [
                    ['terms' => ['_id' => $entityIds]]
                ]
            ]
        ];
    }

    /**
     * @param array $response
     */
    private function logErrors(array $response)
    {
        if (!$this->logger) {
            return;
        }

        $context = ['response' => $response];
        $this->logger->error('Failed reindex into ' . $this->getIndexAgent()->getIndexName(), $context);
    }
}
