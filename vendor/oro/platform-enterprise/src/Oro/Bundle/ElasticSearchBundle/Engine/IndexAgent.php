<?php

namespace Oro\Bundle\ElasticSearchBundle\Engine;

use Oro\Bundle\ElasticSearchBundle\Client\ClientFactory;
use Oro\Bundle\ElasticSearchBundle\Provider\ElasticSearchMappingProvider;
use Oro\Bundle\SearchBundle\Command\ReindexCommand;
use Oro\Bundle\SearchBundle\Engine\Indexer;

class IndexAgent extends AbstractIndexAgent
{
    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var ElasticPluginVerifier
     */
    protected $elasticPluginVerifier;

    /**
     * @var ElasticSearchMappingProvider
     */
    protected $mappingProvider;

    /**
     * @var array
     */
    protected $engineParameters;

    /**
     * @param ClientFactory $clientFactory
     * @param ElasticPluginVerifier $elasticPluginVerifier
     * @param ElasticSearchMappingProvider $mappingProvider
     * @param array $engineParameters
     */
    public function __construct(
        ClientFactory $clientFactory,
        ElasticPluginVerifier $elasticPluginVerifier,
        ElasticSearchMappingProvider $mappingProvider,
        array $engineParameters
    ) {
        $this->clientFactory = $clientFactory;
        $this->elasticPluginVerifier = $elasticPluginVerifier;
        $this->mappingProvider = $mappingProvider;
        $this->engineParameters = $engineParameters;
    }

    /**
     * @return ClientFactory
     */
    protected function getClientFactory()
    {
        return $this->clientFactory;
    }

    /**
     * @return ElasticPluginVerifier
     */
    protected function getElasticPluginVerifier()
    {
        return $this->elasticPluginVerifier;
    }

    /**
     * @return ElasticSearchMappingProvider
     */
    protected function getMappingProvider()
    {
        return $this->mappingProvider;
    }

    /**
     * @return array
     */
    protected function getEngineParameters()
    {
        return $this->engineParameters;
    }

    /**
     * @param array $mapping
     */
    public function setFieldTypeMapping(array $mapping)
    {
        $this->fieldTypeMapping = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function recreateIndex($class = null)
    {
        $client = $this->getClient();
        $this->validateClient();

        if (null === $class) {
            $indexName = $this->getIndexName();
            if ($this->isIndexExists($indexName)) {
                $client->indices()->delete(['index' => $indexName]);
            }

            $client->indices()->create($this->getIndexConfiguration());
            $this->waitForIndexHealthStatus();
        } else {
            $this->validateTypeMapping($class);
            $this->clearType($class);
        }
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getIndexConfiguration()
    {
        $this->ensureIndexNameNotEmpty();

        $engineParameters = $this->getEngineParameters();
        $indexConfiguration = $engineParameters['index'];

        // process settings
        if (empty($indexConfiguration['body']['settings'])) {
            $indexConfiguration['body']['settings'] = [];
        }
        $indexConfiguration['body']['settings']
            = array_replace_recursive($this->getSettings(), $indexConfiguration['body']['settings']);

        // process mappings
        if (empty($indexConfiguration['body']['mappings'])) {
            $indexConfiguration['body']['mappings'] = [];
        }
        $indexConfiguration['body']['mappings']
            = array_replace_recursive($this->getMappings(), $indexConfiguration['body']['mappings']);

        return $indexConfiguration;
    }

    /**
     * @return array
     */
    protected function getMappings()
    {
        $mappings = [];

        foreach (array_keys($this->getMappingProvider()->getMappingConfig()) as $entityName) {
            $mappings = array_merge($mappings, $this->getTypeMapping($entityName));
        }

        return $mappings;
    }

    /**
     * @param string $entityName
     *
     * @return array
     * @throws \LogicException
     */
    protected function getTypeMapping($entityName)
    {
        $configuration = $this->getMappingProvider()->getEntityConfig($entityName);

        if (empty($configuration)) {
            throw new \LogicException(sprintf('Search configuration for %s is not defined', $entityName));
        }

        $properties = [];

        // entity fields properties
        foreach ($this->getFieldsWithTypes($configuration['fields']) as $field => $type) {
            $properties[$field] = $this->getFieldTypeMapping($type);

            if ($type === 'text') {
                $properties[$field] = $this->textFieldConfig;
            }
        }

        // all text field
        $properties[Indexer::TEXT_ALL_DATA_FIELD] = $this->textFieldConfig;
        $alias = $configuration['alias'];

        return [$alias => ['properties' => $properties]];
    }

    /**
     * @param string $entityName
     * @throws \LogicException
     */
    public function validateTypeMapping($entityName)
    {
        $typeMapping = $this->getTypeMapping($entityName);
        $type = current(array_keys($typeMapping));
        $body = current(array_values($typeMapping));

        $indexName = $this->getIndexName();

        if (!$this->isTypeExists($indexName, $type)) {
            $this->getClient()->indices()->putMapping(['index' => $indexName, 'type' => $type, 'body' => $body]);

            return;
        }

        $indexMapping = $this->getClient()->indices()->getMapping(['index' => $indexName, 'type' => $type]);
        $indexProperties = $indexMapping[$indexName]['mappings'][$type]['properties'];
        $typeProperties = $typeMapping[$type]['properties'];

        if ($this->isPropertiesMappingChanged($indexProperties, $typeProperties)) {
            throw new \LogicException(
                'Since ElasticSearch 2.0 it is no longer possible ' .
                'to change existing mappings. ' .
                'You should rebuild the whole index using the command ' .
                ReindexCommand::COMMAND_NAME
            );
        }
    }

    /**
     * @param string $entityName
     * @throws \LogicException
     */
    public function clearType($entityName)
    {
        $indexName = $this->getIndexName();
        $type = $this->getMappingProvider()->getEntityAlias($entityName);
        $body = ['query' => ['match_all' => []]];

        if (!$type) {
            throw new \LogicException(sprintf('Search configuration for %s is not defined', $entityName));
        }

        $typeWasExists = $this->isTypeExists($indexName, $type);

        $this->validateTypeMapping($entityName);

        // ES could throw exception in case when query was done at once after type was created
        if ($typeWasExists) {
            // delete by query API was added by plugin
            $this->getClient()->deleteByQuery(['index' => $indexName, 'type' => $type, 'body' => $body]);
        }
    }
}
