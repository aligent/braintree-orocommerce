<?php

namespace Oro\Bundle\ElasticSearchBundle\Engine;

use Doctrine\Common\Persistence\ManagerRegistry;

use Elasticsearch\Client;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\SearchBundle\Engine\AbstractIndexer;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;

class ElasticSearchIndexer extends AbstractIndexer
{
    /** @var IndexAgent */
    protected $indexAgent;

    /** @var Client */
    protected $client;

    /**
     * @param IndexAgent                   $indexAgent
     * @param ManagerRegistry              $registry
     * @param DoctrineHelper               $doctrineHelper
     * @param ObjectMapper                 $mapper
     * @param EntityNameResolver           $entityNameResolver
     */
    public function __construct(
        IndexAgent $indexAgent,
        ManagerRegistry $registry,
        DoctrineHelper $doctrineHelper,
        ObjectMapper $mapper,
        EntityNameResolver $entityNameResolver
    ) {
        parent::__construct($registry, $doctrineHelper, $mapper, $entityNameResolver);

        $this->indexAgent = $indexAgent;
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity, array $context = [])
    {
        $this->ensureIndexExists();
        return $this->processEntities($entity, true);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity, array $context = [])
    {
        $this->ensureIndexExists();
        return $this->processEntities($entity, false);
    }

    /**
     * {@inheritdoc}
     */
    public function resetIndex($class = null, array $context = [])
    {
        $this->ensureIndexExists();
        $this->indexAgent->recreateIndex($class);
    }

    /**
     * @param object|array $entity
     * @param bool $isSave
     * @return bool
     */
    protected function processEntities($entity, $isSave)
    {
        $entities = $this->getEntitiesArray($entity);
        if (!$entities) {
            return false;
        }

        $body = [];

        foreach ($entities as $entity) {
            $type = $this->getEntityAlias($this->doctrineHelper->getEntityClass($entity));
            $id   = (string) $this->doctrineHelper->getSingleEntityIdentifier($entity);

            if (!$type || !$id) {
                continue;
            }

            // need to recreate index to avoid saving of not used fields
            $indexIdentifier = ['_type' => $type, '_id' => $id];
            $body[]          = ['delete' => $indexIdentifier];

            if ($isSave) {
                $indexData = $this->getIndexData($entity);
                if ($indexData) {
                    $body[] = ['create' => $indexIdentifier];
                    $body[] = $indexData;
                }
            }
        }

        if (!$body) {
            return false;
        }

        $response = $this->getClient()->bulk(['index' => $this->indexAgent->getIndexName(), 'body' => $body]);

        return empty($response['errors']);
    }

    /**
     * @param string $class
     * @return string|null
     */
    protected function getEntityAlias($class)
    {
        $entitiesToAliases = $this->mapper->getEntitiesListAliases();

        return !empty($entitiesToAliases[$class]) ? $entitiesToAliases[$class] : null;
    }

    /**
     * @param object $entity
     * @return array
     */
    protected function getIndexData($entity)
    {
        $indexData = [];
        foreach ($this->mapper->mapObject($entity) as $fields) {
            $indexData = array_merge($indexData, $fields);
        }

        foreach ($indexData as $key => $value) {
            if ($value instanceof \DateTime) {
                $value->setTimezone(new \DateTimeZone('UTC'));
                $indexData[$key] = $value->format('Y-m-d H:i:s');
            } elseif (is_object($value)) {
                $indexData[$key] = (string) $value;
            }
        }

        return $indexData;
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return $this->indexAgent->getClient();
    }

    protected function ensureIndexExists()
    {
        $indexName = $this->indexAgent->getIndexName();
        if (!$this->indexAgent->isIndexExists($indexName)) {
            $this->indexAgent->recreateIndex();
        }
    }
}
