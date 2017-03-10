<?php

namespace Oro\Bundle\ElasticSearchBundle\Engine;

use Elasticsearch\Client;

use Psr\Log\LoggerAwareTrait;

use Oro\Bundle\ElasticSearchBundle\Client\ClientFactory;
use Oro\Bundle\SearchBundle\Provider\AbstractSearchMappingProvider;
use Oro\Bundle\SearchBundle\Query\Query;

abstract class AbstractIndexAgent
{
    use LoggerAwareTrait;

    const FULLTEXT_SEARCH_ANALYZER = 'fulltext_search_analyzer';
    const FULLTEXT_INDEX_ANALYZER = 'fulltext_index_analyzer';
    const FULLTEXT_ANALYZED_FIELD = 'analyzed';

    const DEFAULT_MINIMUM_VERSION = '2.0';
    const DEFAULT_RESTRICTED_VERSION = '3.0';

    /**
     * @var array
     */
    protected $fieldTypeMapping = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $settings = [
        'analysis' => [
            'analyzer' => [
                self::FULLTEXT_SEARCH_ANALYZER => [
                    'tokenizer' => 'whitespace',
                    'filter' => ['lowercase']
                ],
                self::FULLTEXT_INDEX_ANALYZER => [
                    'tokenizer' => 'keyword',
                    'filter' => ['lowercase', 'substring'],
                ],
            ],
            'filter' => [
                'substring' => [
                    'type' => 'nGram',
                    'min_gram' => 1,
                    'max_gram' => 50
                ]
            ],
        ],
        'max_result_window' => Query::INFINITY,
    ];

    /**
     * For text fields we should create non analysed field for strict search (=, != operators)
     * and subfield 'analyzed' for fuzzy search (~, !~ operators)
     *
     * @var array
     */
    protected $textFieldConfig = [
        'type' => 'string',
        'store' => true,
        'index' => 'not_analyzed',
        'fields' => [
            self::FULLTEXT_ANALYZED_FIELD => [
                'type' => 'string',
                'search_analyzer' => self::FULLTEXT_SEARCH_ANALYZER,
                'analyzer' => self::FULLTEXT_INDEX_ANALYZER
            ]
        ]
    ];

    /**
     * Cache client validate to not validate client each time
     *
     * @var bool
     */
    protected $clientValidated = false;

    /**
     * @return ClientFactory
     */
    abstract protected function getClientFactory();

    /**
     * @return ElasticPluginVerifier
     */
    abstract protected function getElasticPluginVerifier();

    /**
     * @return AbstractSearchMappingProvider
     */
    abstract protected function getMappingProvider();

    /**
     * @return array
     */
    abstract protected function getEngineParameters();

    /**
     * @param array $mapping
     */
    abstract public function setFieldTypeMapping(array $mapping);

    /**
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = $this->getClientFactory()->create($this->getClientConfiguration());
        }

        return $this->client;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getIndexName()
    {
        $this->ensureIndexNameNotEmpty();
        $engineParameters = $this->getEngineParameters();

        // index name must be lowercase
        return strtolower($engineParameters['index']['index']);
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function ensureIndexNameNotEmpty()
    {
        $engineParameters = $this->getEngineParameters();

        if (empty($engineParameters['index']['index'])) {
            throw new \InvalidArgumentException('Index name, can not be empty');
        }
    }

    /**
     * @throws \LogicException
     */
    protected function validateClient()
    {
        $engineParameters = $this->getEngineParameters();

        // skip system requirements check and don't validate client
        if (array_key_exists('system_requirements_check', $engineParameters) &&
            $engineParameters['system_requirements_check'] === false
        ) {
            return;
        }

        if ($this->clientValidated) {
            return;
        }

        if (!empty($engineParameters['minimum_required_version'])) {
            $minimumRequiredVersion = $engineParameters['minimum_required_version'];
        } else {
            $minimumRequiredVersion = static::DEFAULT_MINIMUM_VERSION;
        }

        if (!empty($engineParameters['restricted_required_version'])) {
            $restrictedRequiredVersion = $engineParameters['restricted_required_version'];
        } else {
            $restrictedRequiredVersion = static::DEFAULT_RESTRICTED_VERSION;
        }

        $client = $this->getClient();
        $info = $client->info();

        if (empty($info['version']['number'])) {
            throw new \LogicException('Can not receive ElasticSearch version, validation can not be applied');
        }

        if (version_compare($info['version']['number'], $minimumRequiredVersion) < 0) {
            throw new \LogicException(
                sprintf(
                    'ElasticSearch %s is not supported, minimum required version is %s',
                    $info['version']['number'],
                    $minimumRequiredVersion
                )
            );
        }

        if (version_compare($info['version']['number'], $restrictedRequiredVersion) >= 0) {
            throw new \LogicException(
                sprintf(
                    'ElasticSearch %s is not supported, version should be lower than %s',
                    $info['version']['number'],
                    $restrictedRequiredVersion
                )
            );
        }

        $this->getElasticPluginVerifier()->assertPluginsInstalled($client);
        $this->clientValidated = true;
    }

    /**
     * @param string $indexName
     *
     * @return bool
     */
    public function isIndexExists($indexName)
    {
        return $this->getClient()->indices()->exists(['index' => $indexName]);
    }

    /**
     * @param array $indexProperties
     * @param array $typeProperties
     * @return bool
     */
    protected function isPropertiesMappingChanged(array $indexProperties, array $typeProperties)
    {
        $indexKeys = array_keys($indexProperties);
        $typeKeys = array_keys($typeProperties);
        sort($indexKeys);
        sort($typeKeys);

        if ($indexKeys != $typeKeys) {
            return true;
        }

        foreach ($indexProperties as $indexPropertyName => $indexPropertyValues) {
            $typePropertyValues = $typeProperties[$indexPropertyName];

            if ($indexPropertyValues != $typePropertyValues) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getClientConfiguration()
    {
        $engineParameters = $this->getEngineParameters();

        if (!empty($engineParameters['client'])) {
            return $engineParameters['client'];
        }

        return [];
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function getFieldsWithTypes(array $fields)
    {
        $fieldsWithTypes = [];

        foreach ($fields as $field) {
            if (!empty($field['target_type'])) {
                $targetType = $field['target_type'];
                $targetFields = isset($field['target_fields']) ? $field['target_fields'] : [$field['name']];
                foreach ($targetFields as $targetField) {
                    $fieldsWithTypes[$targetField] = $targetType;
                }
            } elseif (!empty($field['relation_fields'])) {
                $fieldsWithTypes = array_merge($fieldsWithTypes, $this->getFieldsWithTypes($field['relation_fields']));
            }
        }

        return $fieldsWithTypes;
    }

    /**
     * @param string $type
     *
     * @return array
     * @throws \LogicException
     */
    protected function getFieldTypeMapping($type)
    {
        if (!array_key_exists($type, $this->fieldTypeMapping)) {
            throw new \LogicException(sprintf('Type mapping for type "%s" is not defined', $type));
        }

        return $this->fieldTypeMapping[$type];
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param string $indexName
     * @param string $typeName
     * @return bool
    */
    public function isTypeExists($indexName, $typeName)
    {
        // incorrect phpdoc in elasticsearch-php library. Explicitly cast to bool
        return (bool)$this->getClient()->indices()->existsType(['index' => $indexName, 'type' => $typeName]);
    }

    /**
     * @param string $status
     */
    public function waitForIndexHealthStatus($status = 'yellow')
    {
        $this->validateClient();

        $index = $this->getIndexName();

        // skip status check and don't request cluster
        $engineParameters = $this->getEngineParameters();
        if (!array_key_exists('index_status_check', $engineParameters) ||
            $engineParameters['index_status_check'] === true
        ) {
            if ($this->logger) {
                $this->logger->debug('Wait for ES index status', ['wait_for_status' => $status, 'index' => $index]);
            }

            // Wait until shards initialized on nodes
            // https://www.elastic.co/guide/en/elasticsearch/guide/current/_cluster_health.html#_blocking_for_status_changes
            $this->getClient()->cluster()->health([
                'wait_for_status' => $status,
                'index' => $index,
            ]);
        }

        // Safety check
        if (!$this->isIndexExists($index)) {
            throw new \LogicException(sprintf('Index %s does not exist', $index));
        }
    }

    /**
     * Refresh index to allow data to appear at the index
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/guide/current/near-real-time.html
     * @param string|null $index
     */
    public function refreshIndex($index = null)
    {
        $this->validateClient();

        if (!$index) {
            $index = $this->getIndexName();
        }

        if ($this->logger) {
            $this->logger->debug('Refresh ES index', ['index' => $index]);
        }

        $this->getClient()->indices()->refresh([
            'index' => $index
        ]);
    }
}
