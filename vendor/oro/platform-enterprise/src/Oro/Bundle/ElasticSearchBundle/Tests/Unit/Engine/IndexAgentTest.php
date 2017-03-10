<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\Engine;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\ElasticSearchBundle\Provider\ElasticSearchMappingProvider;
use Oro\Bundle\ElasticSearchBundle\Client\ClientFactory;
use Oro\Bundle\ElasticSearchBundle\Engine\ElasticPluginVerifier;
use Oro\Bundle\ElasticSearchBundle\Engine\MappingValidator;
use Oro\Bundle\ElasticSearchBundle\Engine\IndexAgent;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class IndexAgentTest extends AbstractIndexAgentTestCase
{
    public function testRecreateIndexWithIndexExist()
    {
        $indexConfiguration = $this->getIndexConfiguration();

        $indices = $this->getIndices();

        $indices->expects($this->any())
            ->method('exists')
            ->with(['index' => $this->testIndexName])
            ->willReturn(true);

        $indices->expects($this->once())->method('delete')->with(['index' => $this->testIndexName]);
        $indices->expects($this->once())->method('create')->with($indexConfiguration);

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

        $client->expects($this->any())->method('indices')->willReturn($indices);

        $cluster = $this->getCluster();
        $cluster->expects($this->once())
            ->method('health')
            ->withConsecutive(
                [['wait_for_status' => 'yellow', 'index' => $this->testIndexName]]
            );

        $client->expects($this->any())->method('cluster')
            ->will($this->returnValue($cluster));

        $pluginVerifier = $this->getMockBuilder(ElasticPluginVerifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pluginVerifier->expects($this->once())
            ->method('assertPluginsInstalled')
            ->with($client);

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
        $indexConfiguration = $this->getIndexConfiguration();

        $indices = $this->getIndices();

        $indices->expects($this->at(0))
            ->method('exists')
            ->with(['index' => $this->testIndexName])
            ->willReturn(false);

        $indices->expects($this->never())->method('delete');
        $indices->expects($this->once())->method('create')->with($indexConfiguration);

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

        $client->expects($this->any())->method('indices')->willReturn($indices);

        $cluster = $this->getCluster();
        $cluster->expects($this->once())
            ->method('health')
            ->with(['wait_for_status' => 'yellow', 'index' => $this->testIndexName]);

        $client->expects($this->any())->method('cluster')
            ->will($this->returnValue($cluster));

        $indices->expects($this->at(2))
            ->method('exists')
            ->with(['index' => $this->testIndexName])
            ->willReturn(true);

        $pluginVerifier = $this->getMockBuilder(ElasticPluginVerifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pluginVerifier->expects($this->once())
            ->method('assertPluginsInstalled')
            ->with($client);

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

    public function testRecreateIndexWithClass()
    {
        $indices = $this->getIndices();

        $indices->expects($this->exactly(3))
            ->method('existsType')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn(false);

        $indices->expects($this->exactly(2))
            ->method('putMapping')
            ->with([
                'index' => $this->testIndexName,
                'type' => $this->type,
                'body' => $this->getMappingBody()
            ]);

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

        $client->expects($this->any())->method('indices')->willReturn($indices);

        $pluginVerifier = $this->getMockBuilder(ElasticPluginVerifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pluginVerifier->expects($this->once())
            ->method('assertPluginsInstalled')
            ->with($client);

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
        $indexAgent->recreateIndex($this->entityName);
    }

    /**
     * @return array
     */
    protected function getIndexConfiguration()
    {
        return [
            'index' => $this->testIndexName,
            'body' => [
                'settings' => $this->settings,
                'mappings' => [
                    'oro_test_entity' => [
                        'properties' => [
                            'property' => $this->typeMapping['text'],
                            'all_text' => $this->allTextMapping,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param object|null $clientFactory
     * @param object|null $pluginVerifier
     * @param array $engineParameters
     * @param array $entityConfiguration
     * @return IndexAgent
     */
    protected function createIndexAgent(
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

        /** @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()->getMock();
        $mapperProvider = new ElasticSearchMappingProvider($eventDispatcher);
        $mapperProvider->setMappingConfig($entityConfiguration);

        $mappingValidator = $this->createMock(MappingValidator::class);
        $mapperProvider->setMappingValidator($mappingValidator);

        return new IndexAgent(
            $clientFactory,
            $pluginVerifier,
            $mapperProvider,
            $engineParameters
        );
    }

    public function testValidateTypeMappingDoNothing()
    {
        $indices = $this->getIndices();
        $indices->expects($this->once())->method('existsType')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn(true);
        $indices->expects($this->never())->method('putMapping')
            ->with([
                'index' => $this->testIndexName,
                'type' => $this->type,
                'body' => $this->getMappingBody()
            ]);
        $indices->expects($this->once())->method('getMapping')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn([
                $this->testIndexName => [
                    'mappings' => [
                        $this->type => $this->getMappingBody()
                    ]
                ]
            ]);

        $this->getValidateIndexAgent($indices)->validateTypeMapping($this->entityName);
    }

    public function testValidateTypeMappingNewMapping()
    {
        $indices = $this->getIndices();

        $indices->expects($this->once())
            ->method('existsType')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn(false);

        $indices->expects($this->once())
            ->method('putMapping')
            ->with([
                'index' => $this->testIndexName,
                'type' => $this->type,
                'body' => $this->getMappingBody()
            ]);

        $indices->expects($this->never())
            ->method('getMapping')
            ->with(['index' => $this->testIndexName, 'type' => $this->type]);

        $this->getValidateIndexAgent($indices)->validateTypeMapping($this->entityName);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Type mapping for type "text" is not defined
     */
    public function testValidateTypeMappingExceptionTypeMappingNotExist()
    {
        $indexAgent = $this->createIndexAgent(
            null,
            null,
            [
                'index' => [
                    'index' => $this->testIndexName
                ]
            ],
            $this->getEntityConfiguration()
        );

        $indexAgent->validateTypeMapping($this->entityName);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Since ElasticSearch 2.0 it is no longer possible to change existing mappings. You should rebuild the whole index using the command oro:search:reindex
     */
    // @codingStandardsIgnoreEnd
    public function testValidateTypeMappingChangedMapping()
    {
        $indices = $this->getIndices();
        $indices->expects($this->once())
            ->method('existsType')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn(true);

        $indices->expects($this->never())
            ->method('putMapping')
            ->with([
                'index' => $this->testIndexName,
                'type' => $this->type,
                'body' => $this->getMappingBody()
            ]);

        $indices->expects($this->once())
            ->method('getMapping')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn([
                $this->testIndexName => [
                    'mappings' => [
                        $this->type => [
                            'properties' => [
                                'newProperty' => $this->typeMapping['text'],
                                'all_text' => $this->allTextMapping,
                            ],
                        ]
                    ]
                ]
            ]);

        $this->getValidateIndexAgent($indices)->validateTypeMapping($this->entityName);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Search configuration for UnknownEntity is not defined
     */
    public function testValidateTypeMappingUnknownEntity()
    {
        $indexAgent = $this->createIndexAgent();
        $indexAgent->setFieldTypeMapping($this->typeMapping);
        $indexAgent->validateTypeMapping('UnknownEntity');
    }

    public function testClearType()
    {
        $expectedRequest = [
            'index' => $this->testIndexName,
            'type' => $this->type,
            'body' => ['query' => ['match_all' => []]]
        ];

        $indices = $this->getIndices();
        $indices->expects($this->exactly(2))
            ->method('existsType')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn(true);

        $indices->expects($this->once())
            ->method('getMapping')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn([
                $this->testIndexName => [
                    'mappings' => [
                        $this->type => $this->getMappingBody()
                    ]
                ]
            ]);

        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);
        $client->expects($this->once())
            ->method('deleteByQuery')
            ->with($expectedRequest);

        $client->expects($this->any())->method('indices')->willReturn($indices);

        $agent = $this->createIndexAgent(
            $clientFactory,
            null,
            [
                'index' => [
                    'index' => $this->testIndexName
                ]
            ],
            $this->getEntityConfiguration()
        );
        $agent->setFieldTypeMapping($this->typeMapping);

        $agent->clearType($this->entityName);
    }


    public function testClearTypeWithoutType()
    {
        $indices = $this->getIndices();
        $indices->expects($this->exactly(2))
            ->method('existsType')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn(false);

        $indices->expects($this->never())
            ->method('getMapping');

        $indices->expects($this->once())
            ->method('putMapping')
            ->with(['index' => $this->testIndexName, 'type' => $this->type, 'body' => $this->getMappingBody()]);

        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);
        $client->expects($this->any())->method('indices')->willReturn($indices);
        $client->expects($this->never())
            ->method('deleteByQuery');

        $agent = $this->createIndexAgent(
            $clientFactory,
            null,
            [
                'index' => [
                    'index' => $this->testIndexName
                ]
            ],
            $this->getEntityConfiguration()
        );
        $agent->setFieldTypeMapping($this->typeMapping);

        $agent->clearType($this->entityName);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Search configuration for TestEntity is not defined
     */
    public function testClearTypeWithUnknownEntity()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $clientFactory->expects($this->never())
            ->method('create')
            ->with([])
            ->willReturn($client);

        $client->expects($this->never())
            ->method('deleteByQuery');

        $agent = $this->createIndexAgent($clientFactory, null, ['index' => ['index' => 'index']], []);
        $agent->clearType('TestEntity');
    }

    /**
     * @param IndicesNamespace|\PHPUnit_Framework_MockObject_MockObject $indices
     * @return IndexAgent
     */
    protected function getValidateIndexAgent($indices)
    {
        $index = [
            'index' => [
                'index' => $this->testIndexName
            ]
        ];

        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);

        $client->expects($this->any())->method('indices')->willReturn($indices);

        $indexAgent = $this->createIndexAgent($clientFactory, null, $index, $this->getEntityConfiguration());
        $indexAgent->setFieldTypeMapping($this->typeMapping);

        return $indexAgent;
    }

    /**
     * @param bool $typeExists
     * @dataProvider isTypeExistsProvider
     */
    public function testIsTypeExists($typeExists)
    {
        $index = [
            'index' => [
                'index' => $this->testIndexName
            ]
        ];

        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $indices = $this->getIndices();
        $indices->expects($this->once())
            ->method('existsType')
            ->with(['index' => $this->testIndexName, 'type' => $this->type])
            ->willReturn($typeExists);

        $client = $this->getClientMock($clientFactory);
        $client->expects($this->any())->method('indices')->willReturn($indices);

        $indexAgent = $this->createIndexAgent($clientFactory, null, $index, $this->getEntityConfiguration());
        $actualResult = $indexAgent->isTypeExists($this->testIndexName, $this->type);
        $this->assertInternalType('bool', $actualResult);
        $this->assertSame($typeExists, $actualResult);
    }

    /**
     * @return array
     */
    public function isTypeExistsProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @return array
     */
    protected function getEntityConfiguration()
    {
        return [
            $this->entityName => [
                'alias' => $this->type,
                'fields' => [['name' => 'property', 'target_type' => 'text']]
            ]
        ];
    }
}
