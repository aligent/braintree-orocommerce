<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\Engine;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\ClusterNamespace;
use Elasticsearch\Namespaces\IndicesNamespace;

use Oro\Bundle\ElasticSearchBundle\Client\ClientFactory;
use Oro\Bundle\ElasticSearchBundle\Engine\ElasticPluginVerifier;
use Oro\Bundle\ElasticSearchBundle\Engine\IndexAgent;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
abstract class AbstractIndexAgentTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $testIndexName = 'custom_index';

    /**
     * @var string
     */
    protected $entityName = 'Test\Entity';

    /**
     * @var string
     */
    protected $type = 'oro_test_entity';

    /**
     * @var array
     */
    protected $typeMapping = [
        'text' => [
            'type' => 'string',
            'store' => true,
            'index' => 'not_analyzed',
            'fields' => [
                'analyzed' => [
                    'type'            => 'string',
                    'search_analyzer' => IndexAgent::FULLTEXT_SEARCH_ANALYZER,
                    'analyzer'  => IndexAgent::FULLTEXT_INDEX_ANALYZER
                ]
            ]
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

    /**
     * @var array
     */
    protected $allTextMapping = [
        'type'            => 'string',
        'store'           => true,
        'index' => 'not_analyzed',
        'fields' => [
            'analyzed' => [
                'type'            => 'string',
                'search_analyzer' => IndexAgent::FULLTEXT_SEARCH_ANALYZER,
                'analyzer'  => IndexAgent::FULLTEXT_INDEX_ANALYZER
            ]
        ]
    ];

    /**
     * @var array
     */
    protected $settings = [
        'max_result_window' => 10000000,
        'analysis' => [
            'analyzer' => [
                'fulltext_search_analyzer' => [
                    'tokenizer' => 'whitespace',
                    'filter'    => ['lowercase']
                ],
                'fulltext_index_analyzer' => [
                    'tokenizer' => 'keyword',
                    'filter'    => ['lowercase', 'substring'],
                ]
            ],
            'filter' => [
                'substring' => [
                    'type'     => 'nGram',
                    'min_gram' => 1,
                    'max_gram' => 50
                ]
            ],
        ],
    ];

    /**
     * @param ClientFactory|\PHPUnit_Framework_MockObject_MockObject $clientFactory
     * @return Client|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock($clientFactory)
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $clientFactory->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($client);

        return $client;
    }

    /**
     * @param object|null $clientFactory
     * @param object|null $pluginVerifier
     * @param array $engineParameters
     * @param array $entityConfiguration
     * @return IndexAgent
     */
    abstract protected function createIndexAgent(
        $clientFactory = null,
        $pluginVerifier = null,
        array $engineParameters = [],
        array $entityConfiguration = []
    );

    public function testGetIndexName()
    {
        $engineParameters = [
            'index' => [
                'index' => 'Custom_Index'
            ]
        ];

        $indexAgent = $this->createIndexAgent(null, null, $engineParameters);
        $this->assertEquals('custom_index', $indexAgent->getIndexName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Index name, can not be empty
     */
    public function testGetIndexNameWithException()
    {
        $indexAgent = $this->createIndexAgent(null, null, []);
        $indexAgent->getIndexName();
    }

    /**
     * @param array $engineParameters
     * @param array $entityConfiguration
     * @param array $clientConfiguration
     * @dataProvider initializeClientDataProvider
     */
    public function testGetClient(
        array $engineParameters,
        array $entityConfiguration,
        array $clientConfiguration
    ) {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $clientFactory
            ->expects($this->exactly(1))
            ->method('create')
            ->with($clientConfiguration)
            ->will($this->returnValue($client));

        $indexAgent = $this->createIndexAgent($clientFactory, null, $engineParameters, $entityConfiguration);
        $indexAgent->setFieldTypeMapping($this->typeMapping);
        $this->assertEquals($client, $indexAgent->getClient());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function initializeClientDataProvider()
    {
        $minGram = 4;
        $maxGram = 20;

        return [
            'minimum' => [
                'engineParameters' => ['index' => ['index' => 'oro_search']],
                'entityConfiguration' => [
                    'Test\Entity' => [
                        'alias' => 'oro_test_entity',
                        'fields' => [['name' => 'property', 'target_type' => 'text']]
                    ]
                ],
                'clientConfiguration' => []
            ],
            'maximum' => [
                'engineParameters' => [
                    'client' => [
                        'hosts' => ['1.2.3.4'],
                        'logging' => true,
                    ],
                    'index' => [
                        'index' => 'custom_index_name',
                        'body' => [
                            'settings' => [
                                'analysis' => [
                                    'filter' => ['substring' => ['min_gram' => $minGram, 'max_gram' => $maxGram]]
                                ]
                            ]
                        ],
                    ]
                ],
                'entityConfiguration' => [
                    'Test\Entity' => [
                        'alias' => 'oro_test_entity',
                        'fields' => [
                            ['name' => 'name',      'target_type' => 'text'],
                            ['name' => 'price',     'target_type' => 'decimal'],
                            ['name' => 'count',     'target_type' => 'integer'],
                            ['name' => 'createdAt', 'target_type' => 'datetime'],
                            [
                                'name'            => 'relatedEntity',
                                'relation_fields' => [
                                    ['name' => 'firstName', 'target_type' => 'text'],
                                    ['name' => 'lastName',  'target_type' => 'text'],
                                ]
                            ]
                        ],
                    ],
                ],
                'clientConfiguration' => [
                    'hosts' => ['1.2.3.4'],
                    'logging' => true,
                ]
            ],
        ];
    }

    public function testMinimumVersionExceptionOnRecreateIndex()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);
        $elasticServerVersion = '1.7';
        $minimumRequiredVersion = '2.3';

        $client->expects($this->once())
            ->method('info')
            ->willReturn([
                'version' => [
                    'number' => $elasticServerVersion
                ]
            ]);

        $clientFactory->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($client);

        $engineParams = ['minimum_required_version' => $minimumRequiredVersion];
        $indexAgent = $this->createIndexAgent($clientFactory, null, $engineParams, []);

        $this->expectException('\LogicException');
        $this->expectExceptionMessage(
            "ElasticSearch $elasticServerVersion is not supported, minimum required version is $minimumRequiredVersion"
        );

        $indexAgent->recreateIndex($this->entityName);

        $this->assertAttributeSame(true, 'clientValidated', $indexAgent);
    }

    public function testRestrictedVersionExceptionOnRecreateIndex()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);
        $elasticServerVersion = '5.1';
        $restrictedVersion = '3.0';

        $client->expects($this->once())
            ->method('info')
            ->willReturn([
                'version' => [
                    'number' => $elasticServerVersion
                ]
            ]);

        $clientFactory->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($client);

        $engineParams = ['restricted_required_version' => $restrictedVersion];
        $indexAgent = $this->createIndexAgent($clientFactory, null, $engineParams, []);

        $this->expectException('\LogicException');
        $this->expectExceptionMessage(
            "ElasticSearch $elasticServerVersion is not supported, version should be lower than $restrictedVersion"
        );

        $indexAgent->recreateIndex($this->entityName);

        $this->assertAttributeSame(true, 'clientValidated', $indexAgent);
    }

    public function testDisabledSystemRequirementsCheck()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $indices = $this->getIndices();

        $client = $this->getClientMock($clientFactory);
        $client->expects($this->any())
            ->method('indices')
            ->willReturn($indices);
        $client->expects($this->never())
            ->method('info');

        $engineParams = [
            'index' => ['index' => $this->testIndexName],
            'system_requirements_check' => false,
        ];

        $indexAgent = $this->createIndexAgent($clientFactory, null, $engineParams, []);
        $indexAgent->refreshIndex();

        $this->assertAttributeSame(false, 'clientValidated', $indexAgent);
    }

    public function testDisabledIndexStatusCheck()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $indices = $this->getIndices();
        $indices->expects($this->any())
            ->method('exists')
            ->with(['index' => $this->testIndexName])
            ->willReturn(true);

        $client = $this->getClientMock($clientFactory);
        $client->expects($this->any())
            ->method('indices')
            ->willReturn($indices);
        $client->expects($this->never())
            ->method('cluster');

        $pluginVerifier = $this->getMockBuilder(ElasticPluginVerifier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginVerifier->expects($this->never())
            ->method('assertPluginsInstalled');

        $engineParams = [
            'index' => ['index' => $this->testIndexName],
            'system_requirements_check' => false,
            'index_status_check' => false,
        ];

        $indexAgent = $this->createIndexAgent($clientFactory, $pluginVerifier, $engineParams, []);
        $indexAgent->waitForIndexHealthStatus();
    }

    public function testWaitForIndexHealthStatusNoIndexException()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $indices = $this->getIndices();
        $indices->expects($this->once())
            ->method('exists')
            ->with(['index' => $this->testIndexName])
            ->willReturn(false);

        $client = $this->getClientMock($clientFactory);
        $client->expects($this->any())
            ->method('indices')
            ->willReturn($indices);

        $engineParams = [
            'index' => ['index' => $this->testIndexName],
            'system_requirements_check' => false,
            'index_status_check' => false,
        ];

        $this->expectException('\LogicException');
        $this->expectExceptionMessage("Index $this->testIndexName does not exist");

        $indexAgent = $this->createIndexAgent($clientFactory, null, $engineParams, []);
        $indexAgent->waitForIndexHealthStatus();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Can not receive ElasticSearch version, validation can not be applied
     */
    public function testNotReceivedVersionException()
    {
        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getClientMock($clientFactory);

        $client->expects($this->once())->method('info')->willReturn([]);

        $indexAgent = $this->createIndexAgent($clientFactory);

        $indexAgent->recreateIndex($this->entityName);
    }

    /**
     * @return array
     */
    protected function getMappingBody()
    {
        return [
            'properties' => [
                'property' => $this->typeMapping['text'],
                'all_text' => $this->allTextMapping,
            ],
        ];
    }

    /**
     * @return IndicesNamespace|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIndices()
    {
        return $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return ClusterNamespace|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCluster()
    {
        return $this->getMockBuilder(ClusterNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
