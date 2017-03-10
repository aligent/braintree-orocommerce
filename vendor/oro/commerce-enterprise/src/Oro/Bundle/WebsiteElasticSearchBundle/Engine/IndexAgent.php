<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Engine;

use Oro\Bundle\ElasticSearchBundle\Client\ClientFactory;
use Oro\Bundle\ElasticSearchBundle\Engine\AbstractIndexAgent;
use Oro\Bundle\ElasticSearchBundle\Engine\ElasticPluginVerifier;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\WebsiteElasticSearchBundle\Provider\WebsiteElasticSearchMappingProvider;
use Oro\Bundle\WebsiteSearchBundle\Engine\AbstractIndexer;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\PlaceholderInterface;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\WebsiteIdPlaceholder;

class IndexAgent extends AbstractIndexAgent
{
    const TMP_ALIAS_FIELD = 'tmp_alias';

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var ElasticPluginVerifier
     */
    protected $elasticPluginVerifier;

    /**
     * @var WebsiteElasticSearchMappingProvider
     */
    protected $mappingProvider;

    /**
     * @var array
     */
    protected $engineParameters;

    /**
     * @var PlaceholderInterface
     */
    private $placeholderDecorator;

    /**
     * @var WebsiteIdPlaceholder
     */
    private $websiteIdPlaceholder;

    /**
     * @param ClientFactory $clientFactory
     * @param ElasticPluginVerifier $elasticPluginVerifier
     * @param WebsiteElasticSearchMappingProvider $mappingProvider
     * @param array $engineParameters
     * @param PlaceholderInterface $placeholderDecorator
     * @param WebsiteIdPlaceholder $websiteIdPlaceholder
     */
    public function __construct(
        ClientFactory $clientFactory,
        ElasticPluginVerifier $elasticPluginVerifier,
        WebsiteElasticSearchMappingProvider $mappingProvider,
        array $engineParameters,
        PlaceholderInterface $placeholderDecorator,
        WebsiteIdPlaceholder $websiteIdPlaceholder
    ) {
        $this->clientFactory = $clientFactory;
        $this->elasticPluginVerifier = $elasticPluginVerifier;
        $this->mappingProvider = $mappingProvider;
        $this->engineParameters = $engineParameters;
        $this->placeholderDecorator = $placeholderDecorator;
        $this->websiteIdPlaceholder = $websiteIdPlaceholder;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClientFactory()
    {
        return $this->clientFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function getElasticPluginVerifier()
    {
        return $this->elasticPluginVerifier;
    }

    /**
     * @return WebsiteElasticSearchMappingProvider
     */
    protected function getMappingProvider()
    {
        return $this->mappingProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEngineParameters()
    {
        return $this->engineParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldTypeMapping(array $mapping)
    {
        $this->fieldTypeMapping = $mapping;
    }

    /**
     * @return PlaceholderInterface
     */
    protected function getPlaceholderDecorator()
    {
        return $this->placeholderDecorator;
    }

    /**
     * @return WebsiteIdPlaceholder
     */
    protected function getWebsiteIdPlaceholder()
    {
        return $this->websiteIdPlaceholder;
    }

    public function recreateIndex()
    {
        $this->validateClient();

        $indexName = $this->getIndexName();
        if ($this->isIndexExists($indexName)) {
            $this->getClient()->indices()->delete(['index' => $indexName]);
        }

        $this->createIndexWithSettings();
    }

    /**
     * @param string $class
     * @param array $context
     */
    public function createMappings($class, array $context = [])
    {
        $this->validateClient();

        if (empty($context[AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY])) {
            throw new \LogicException('Website id is required');
        }

        $websiteId = $context[AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY];
        $mappingConfig = $this->getMappingProvider()->getEntityConfig($class);

        $this->getClient()->indices()->putMapping($this->getIndexConfig($mappingConfig, $websiteId));
    }

    /**
     * @param array $mappingConfig
     * @param int $websiteId
     * @return array
     */
    private function getIndexConfig(array $mappingConfig, $websiteId)
    {
        $this->ensureIndexNameNotEmpty();

        $engineParameters = $this->getEngineParameters();
        $indexConfiguration = $engineParameters['index'];

        if (empty($mappingConfig['alias'])) {
            throw new \LogicException('Add "alias" for entity mapping');
        }

        $realAlias = $this->getWebsiteIdPlaceholder()->replace(
            $mappingConfig['alias'],
            [
                WebsiteIdPlaceholder::NAME => $websiteId
            ]
        );

        $indexConfiguration['type'] = $realAlias;
        $indexConfiguration['body'] = $this->getMapping($mappingConfig['fields']);

        return $indexConfiguration;
    }

    /**
     * @param array $fields
     *
     * @return array
     * @throws \LogicException
     */
    protected function getMapping(array $fields)
    {
        $dynamicMapping = [];
        $properties = [];

        foreach ($this->getFieldsWithType($fields) as $field => $type) {
            $realField = $this->getPlaceholderDecorator()->replaceDefault($field);

            if ($realField !== $field) {
                $dynamicMapping[][$field] = $this->getDynamicMapping($type, $realField);
                continue;
            }

            if ($type === Query::TYPE_TEXT) {
                $mappingConfig = $this->textFieldConfig;
            } else {
                $mappingConfig = $this->getFieldTypeMapping($type);
            }

            $properties[$field] = $mappingConfig;
        }

        $properties[self::TMP_ALIAS_FIELD]    = $this->getFieldTypeMapping(Query::TYPE_TEXT);

        return [
            'dynamic_templates' => $dynamicMapping,
            'properties' => $properties
        ];
    }

    /**
     * @param string $type
     * @param string $realField
     * @return array
     */
    protected function getDynamicMapping($type, $realField)
    {
        if ($type === Query::TYPE_TEXT) {
            $mappingSettings = $this->textFieldConfig;
        } else {
            $mappingSettings = $this->getFieldTypeMapping($type);
        }

        return [
            'match_pattern' => 'regex',
            'match' => sprintf('^%s$', $realField),
            'match_mapping_type' => 'string',
            'mapping' => $mappingSettings,
        ];
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function getFieldsWithType(array $fields)
    {
        $fieldsWithTypes = [];

        foreach ($fields as $field) {
            if (!empty($field['type']) && !empty($field['name'])) {
                $fieldsWithTypes[$field['name']] = $field['type'];
            }
        }

        return $fieldsWithTypes;
    }

    /**
     * Create index and add settings there
     */
    public function createIndexWithSettings()
    {
        $this->ensureIndexNameNotEmpty();
        $this->validateClient();

        $engineParameters = $this->getEngineParameters();
        $indexConfiguration = $engineParameters['index'];

        // process settings
        if (empty($indexConfiguration['body']['settings'])) {
            $indexConfiguration['body']['settings'] = [];
        }
        $indexConfiguration['body']['settings']
            = array_replace_recursive($this->getSettings(), $indexConfiguration['body']['settings']);

        $client = $this->getClient();
        $client->indices()->create($indexConfiguration);

        $this->waitForIndexHealthStatus();
    }

    public function ensureIndexExists()
    {
        $indexName = $this->getIndexName();
        if (!$this->isIndexExists($indexName)) {
            $this->recreateIndex();
        }
    }
}
