<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Tests\Unit\Engine;

use Elasticsearch\Client;

use Oro\Bundle\WebsiteElasticSearchBundle\Provider\WebsiteElasticSearchMappingProvider;
use Oro\Bundle\WebsiteSearchBundle\Engine\AbstractIndexer;
use Oro\Bundle\ElasticSearchBundle\Client\ClientFactory;
use Oro\Bundle\ElasticSearchBundle\Engine\ElasticPluginVerifier;
use Oro\Bundle\ElasticSearchBundle\Tests\Unit\Engine\AbstractIndexAgentTestCase;
use Oro\Bundle\WebsiteElasticSearchBundle\Engine\IndexAgent;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\PlaceholderInterface;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\WebsiteIdPlaceholder;

class IndexAgentTest extends AbstractIndexAgentTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PlaceholderInterface */
    protected $placeholderDecorator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PlaceholderInterface */
    protected $websiteIdPlaceholder;

    /** @var WebsiteElasticSearchMappingProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $mapperProvider;

    /** @var array */
    protected $typeMapping = [
        'text' => [
            'type' => 'string',
            'store' => true,
            'index' => 'not_analyzed',
        ],
        'decimal' => [
            'type'  => 'double',
            'store' => true,
        ],
        'integer' => [
            'type'  => 'integer',
            'store' => true,
        ],
        'datetime' => [
            'type' => 'date',
            'store' => true,
            'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd'
        ],
    ];

    public function testRecreateIndexWithIndexExist()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);

        $client->expects($this->once())
            ->method('info')
            ->willReturn([
                'version' => [
                    'number' => '2.3'
                ]
            ]);

        $indices = $this->getIndices();

        $indices->expects($this->any())
            ->method('exists')
            ->with(['index' => $this->testIndexName])
            ->willReturn(true);

        $indices->expects($this->once())
            ->method('delete')
            ->with(['index' => $this->testIndexName]);

        $client->expects($this->any())->method('indices')->willReturn($indices);

        $cluster = $this->getCluster();
        $cluster->expects($this->once())
            ->method('health')
            ->with(['wait_for_status' => 'yellow', 'index' => $this->testIndexName]);

        $client->expects($this->any())
            ->method('cluster')
            ->willReturn($cluster);

        $pluginVerifier = $this->prepareRecreateVerifier($client);

        $indexAgent = $this->createIndexAgent(
            $clientFactory,
            $pluginVerifier,
            [
                'index' => [
                    'index' => $this->testIndexName
                ]
            ],
            $this->getEntityConfiguration()
        );

        $indexAgent->setFieldTypeMapping($this->typeMapping);
        $indexAgent->recreateIndex();
    }

    public function testRecreateIndexWithoutIndex()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);

        $client->expects($this->once())
            ->method('info')
            ->willReturn([
                'version' => [
                    'number' => '2.3'
                ]
            ]);

        $indices = $this->getIndices();

        $indices->expects($this->at(0))
            ->method('exists')
            ->with(['index' => $this->testIndexName])
            ->willReturn(false);

        $indices->expects($this->never())
            ->method('delete');

        $indexWithSettingsConfig = [
            'index' => $this->testIndexName,
            'body' => [
                'settings' => $this->settings
            ]
        ];

        $indices->expects($this->once())
            ->method('create')
            ->with($indexWithSettingsConfig);

        $client->expects($this->any())->method('indices')->willReturn($indices);

        $cluster = $this->getCluster();
        $cluster->expects($this->once())
            ->method('health')
            ->with(['wait_for_status' => 'yellow', 'index' => $this->testIndexName]);

        $client->expects($this->any())
            ->method('cluster')
            ->willReturn($cluster);

        $indices->expects($this->at(2))
            ->method('exists')
            ->with(['index' => $this->testIndexName])
            ->willReturn(true);

        $pluginVerifier = $this->prepareRecreateVerifier($client);

        $indexAgent = $this->createIndexAgent(
            $clientFactory,
            $pluginVerifier,
            [
                'index' => [
                    'index' => $this->testIndexName
                ]
            ],
            $this->getEntityConfiguration()
        );

        $indexAgent->setFieldTypeMapping($this->typeMapping);
        $indexAgent->recreateIndex();
    }

    public function testCreateMappingsWithDynamicMapping()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);

        $client->expects($this->once())
            ->method('info')
            ->willReturn([
                'version' => [
                    'number' => '2.3'
                ]
            ]);

        $indices = $this->getIndices();

        $indices->expects($this->once())
            ->method('putMapping')
            ->with($this->getDynamicAndSimpleMapping());

        $client->expects($this->any())->method('indices')->willReturn($indices);

        $pluginVerifier = $this->prepareRecreateVerifier($client);

        $indexAgent = $this->createIndexAgent(
            $clientFactory,
            $pluginVerifier,
            [
                'index' => [
                    'index' => $this->testIndexName
                ]
            ],
            $this->getEntityConfiguration()
        );

        $this->mapperProvider->expects($this->once())
            ->method('getEntityConfig')
            ->with($this->entityName)
            ->willReturn($this->getEntityConfiguration()[$this->entityName]);

        $this->websiteIdPlaceholder->expects($this->once())
            ->method('replace')
            ->with('oro_test_entity_WEBSITE_ID', [WebsiteIdPlaceholder::NAME => 1])
            ->willReturn('oro_test_entity_1');

        $this->placeholderDecorator->expects($this->exactly(4))
            ->method('replaceDefault')
            ->withConsecutive(
                ['property'],
                ['description_LOCALIZATION_ID'],
                ['integer_field'],
                ['integer_dynamic_LOCALIZATION_ID']
            )
            ->willReturnOnConsecutiveCalls('property', 'description_[0-9]', 'integer_field', 'integer_dynamic_[0-9]');

        $indexAgent->setFieldTypeMapping($this->typeMapping);
        $indexAgent->createMappings($this->entityName, [AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY => 1]);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Add "alias" for entity mapping
     */
    public function testCreateMappingsWithAliasException()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);

        $client->expects($this->once())
            ->method('info')
            ->willReturn([
                'version' => [
                    'number' => '2.3'
                ]
            ]);

        $indices = $this->getIndices();

        $client->expects($this->any())->method('indices')->willReturn($indices);

        $pluginVerifier = $this->prepareRecreateVerifier($client);

        $indexAgent = $this->createIndexAgent(
            $clientFactory,
            $pluginVerifier,
            [
                'index' => [
                    'index' => $this->testIndexName
                ]
            ],
            $this->getEntityConfiguration()
        );

        $this->mapperProvider->expects($this->once())
            ->method('getEntityConfig')
            ->with($this->entityName)
            ->willReturn(['fields' => []]);

        $indexAgent->setFieldTypeMapping($this->typeMapping);
        $indexAgent->createMappings($this->entityName, [AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY => 1]);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Website id is required
     */
    public function testRecreateIndexWithoutWebsiteId()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);

        $client->expects($this->once())
            ->method('info')
            ->willReturn([
                'version' => [
                    'number' => '2.3'
                ]
            ]);

        $pluginVerifier = $this->prepareRecreateVerifier($client);

        $indexAgent = $this->createIndexAgent(
            $clientFactory,
            $pluginVerifier,
            [
                'index' => [
                    'index' => $this->testIndexName
                ]
            ],
            $this->getEntityConfiguration()
        );

        $indexAgent->setFieldTypeMapping($this->typeMapping);
        $indexAgent->createMappings($this->entityName);
    }

    /**
     * @param ClientFactory|null $clientFactory
     * @param ElasticPluginVerifier|null $pluginVerifier
     * @param array $engineParameters
     * @param array $entityConfiguration
     * @return IndexAgent
     */
    public function createIndexAgent(
        $clientFactory = null,
        $pluginVerifier = null,
        array $engineParameters = [],
        array $entityConfiguration = []
    ) {
        if (!$clientFactory) {
            $clientFactory = $this->getMockBuilder(ClientFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        if (!$pluginVerifier) {
            $pluginVerifier = $this->getMockBuilder(ElasticPluginVerifier::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        $this->mapperProvider = $this->getMockBuilder(WebsiteElasticSearchMappingProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->placeholderDecorator = $this->createMock(PlaceholderInterface::class);
        $this->websiteIdPlaceholder = $this->getMockBuilder(WebsiteIdPlaceholder::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new IndexAgent(
            $clientFactory,
            $pluginVerifier,
            $this->mapperProvider,
            $engineParameters,
            $this->placeholderDecorator,
            $this->websiteIdPlaceholder
        );
    }

    /**
     * @return array
     */
    protected function getEntityConfiguration()
    {
        return [
            $this->entityName => [
                'alias' => 'oro_test_entity_WEBSITE_ID',
                'fields' => [
                    [
                        'name' => 'property',
                        'type' => 'text'
                    ],
                    [
                        'name' => 'description_LOCALIZATION_ID',
                        'type' => 'text'
                    ],
                    [
                        'name' => 'integer_field',
                        'type' => 'integer'
                    ],
                    [
                        'name' => 'integer_dynamic_LOCALIZATION_ID',
                        'type' => 'integer'
                    ],
                ]
            ]
        ];
    }

    /**
     * @param Client $client
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareRecreateVerifier($client)
    {
        $pluginVerifier = $this->getMockBuilder(ElasticPluginVerifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pluginVerifier->expects($this->once())
            ->method('assertPluginsInstalled')
            ->with($client);

        return $pluginVerifier;
    }

    /**
     * @return array
     */
    protected function getDynamicAndSimpleMapping()
    {
        return [
            'index' => $this->testIndexName,
            'type' => 'oro_test_entity_1',
            'body' => [
                'dynamic_templates' => [
                    [
                        'description_LOCALIZATION_ID' => [
                            'match_pattern' => 'regex',
                            'match' => '^description_[0-9]$',
                            'match_mapping_type' => 'string',
                            'mapping' => [
                                'type' => 'string',
                                'store' => true,
                                'index' => 'not_analyzed',
                                'fields' => [
                                    'analyzed' => [
                                        'type' => 'string',
                                        'search_analyzer' => 'fulltext_search_analyzer',
                                        'analyzer' => 'fulltext_index_analyzer'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'integer_dynamic_LOCALIZATION_ID' => [
                            'match_pattern' => 'regex',
                            'match' => '^integer_dynamic_[0-9]$',
                            'match_mapping_type' => 'string',
                            'mapping' => [
                                'type' => 'integer',
                                'store' => true
                            ]
                        ]
                    ]
                ],
                'properties' => [
                    'property' => [
                        'type' => 'string',
                        'store' => true,
                        'index' => 'not_analyzed',
                        'fields' => [
                            'analyzed' => [
                                'type' => 'string',
                                'search_analyzer' => 'fulltext_search_analyzer',
                                'analyzer' => 'fulltext_index_analyzer'
                            ]
                        ]
                    ],
                    'integer_field' => [
                        'type' => 'integer',
                        'store' => true
                    ],
                    'tmp_alias' => [
                        'type' => 'string',
                        'store' => true,
                        'index' => 'not_analyzed',
                    ]
                ]
            ]
        ];
    }
}
