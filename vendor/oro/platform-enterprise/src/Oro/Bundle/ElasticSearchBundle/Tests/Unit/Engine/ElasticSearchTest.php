<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\Engine;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ElasticSearchBundle\Engine\IndexAgent;
use Oro\Bundle\ElasticSearchBundle\RequestBuilder\RequestBuilderRegistry;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\ElasticSearchBundle\Engine\ElasticSearch;
use Oro\Bundle\ElasticSearchBundle\Tests\Unit\Stub\TestEntity;

class ElasticSearchTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CLASS = 'Stub\TestEntity';
    const TEST_DESCENDANT_1 = 'Stub\TestChildEntity1';
    const TEST_DESCENDANT_2 = 'Stub\TestChildEntity2';
    const TEST_ALIAS = 'test_entity';
    const TEST_INDEX = 'test_index';

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var ObjectMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapper;

    /**
     * @var IndexAgent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexAgent;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var RequestBuilderRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestBuilderRegistry;

    /**
     * @var ElasticSearch
     */
    protected $engine;

    protected function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->mapper = $this->getMockBuilder(ObjectMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexAgent = $this->getMockBuilder(IndexAgent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestBuilderRegistry = $this->getMockBuilder(RequestBuilderRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->any())->method('mapObject')
            ->with($this->isInstanceOf('Oro\Bundle\ElasticSearchBundle\Tests\Unit\Stub\TestEntity'))
            ->will(
                $this->returnCallback(
                    function (TestEntity $entity) {
                        $map = ['text' => []];
                        if ($entity->name) {
                            $map['text']['name'] = $entity->name;
                        }
                        if ($entity->birthday) {
                            $map['datetime']['birthday'] = $entity->birthday;
                        }
                        if ($entity->entity) {
                            $map['text']['entity'] = $entity->entity;
                        }
                        return $map;
                    }
                )
            );
        $this->mapper->expects($this->any())->method('getEntitiesListAliases')
            ->will($this->returnValue([self::TEST_CLASS => self::TEST_ALIAS]));

        $this->indexAgent->expects($this->any())->method('getIndexName')
            ->will($this->returnValue(self::TEST_INDEX));

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->engine = new ElasticSearch(
            $this->registry,
            $this->eventDispatcher,
            $this->mapper,
            $this->indexAgent,
            $this->requestBuilderRegistry
        );
    }

    /**
     * @param array $response
     * @param array $items
     * @param int $count
     * @dataProvider searchDataProvider
     */
    public function testSearch(array $response, array $items, $count)
    {
        $query = new Query();

        $entityConfiguration = [
            'alias' => self::TEST_ALIAS,
            'fields' => [['name' => 'property', 'target_type' => 'text']]
        ];

        $firstBuilder = $this->createMock('Oro\Bundle\ElasticSearchBundle\RequestBuilder\RequestBuilderInterface');
        $firstBuilder->expects($this->once())->method('build')
            ->with($query, ['index' => self::TEST_INDEX])
            ->will(
                $this->returnCallback(
                    function (Query $query, array $request) {
                        $request['first'] = true;
                        return $request;
                    }
                )
            );
        $secondBuilder = $this->createMock('Oro\Bundle\ElasticSearchBundle\RequestBuilder\RequestBuilderInterface');
        $secondBuilder->expects($this->once())->method('build')
            ->with($query, ['index' => self::TEST_INDEX, 'first' => true])
            ->will(
                $this->returnCallback(
                    function (Query $query, array $request) {
                        $request['second'] = true;
                        return $request;
                    }
                )
            );

        $expectedRequest = ['index' => self::TEST_INDEX, 'first' => true, 'second' => true];

        $client = $this->getMockBuilder('Elasticsearch\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())->method('search')->with($expectedRequest)
            ->will($this->returnValue($response));

        $this->indexAgent->expects($this->any())->method('getIndexName')
            ->will($this->returnValue(self::TEST_INDEX));
        $this->indexAgent->expects($this->once())->method('getClient')
            ->will($this->returnValue($client));

        $this->mapper->expects($this->any())->method('getEntityConfig')->with(self::TEST_CLASS)
            ->will($this->returnValue($entityConfiguration));

        $this->mapper->expects($this->any())->method('mapSelectedData')->willReturn([]);

        $expectedItems = [];
        foreach ($items as $item) {
            $expectedItems[] = new Item(
                $item['class'],
                $item['id'],
                null,
                null,
                [],
                $entityConfiguration
            );
        }

        $this->requestBuilderRegistry->expects($this->once())
            ->method('getRequestBuilders')
            ->willReturn([$firstBuilder, $secondBuilder]);

        $result = $this->engine->search($query);
        $this->assertEquals($query, $result->getQuery());

        $this->assertEquals($expectedItems, $result->getElements());
        $this->assertEquals($count, $result->getRecordsCount());
    }

    /**
     * @return array
     */
    public function searchDataProvider()
    {
        return [
            'valid response' => [
                'response' => [
                    'hits' => [
                        'total' => 5,
                        'hits' => [
                            [
                                '_type' => self::TEST_ALIAS,
                                '_id' => 1,
                                '_source' => [Indexer::TEXT_ALL_DATA_FIELD => 'first']
                            ],
                            ['_type' => self::TEST_ALIAS, '_id' => 2],
                            ['_type' => 'unknown_entity', '_id' => 3],
                            ['_type' => self::TEST_ALIAS],
                        ]
                    ]
                ],
                'items' => [
                    ['class' => self::TEST_CLASS, 'id' => 1],
                    ['class' => self::TEST_CLASS, 'id' => 2],
                ],
                'count' => 5
            ],
            'empty response' => [
                'response' => [
                    'hits' => [
                        'total' => 0,
                        'hits' => []
                    ]
                ],
                'items' => [],
                'count' => 0
            ],
            'invalid response' => [
                'response' => [],
                'items' => [],
                'count' => 0
            ]
        ];
    }
}
