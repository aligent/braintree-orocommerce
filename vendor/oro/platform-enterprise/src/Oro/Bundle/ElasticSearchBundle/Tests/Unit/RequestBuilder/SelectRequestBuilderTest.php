<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\RequestBuilder;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\ElasticSearchBundle\RequestBuilder\SelectRequestBuilder;

class SelectRequestBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array|string $from
     * @param array $originalRequest
     * @param array $expectedRequest
     * @dataProvider buildDataProvider
     */
    public function testBuild($from, array $originalRequest, array $expectedRequest)
    {
        $query = new Query();
        $query->select($from);

        $builder = new SelectRequestBuilder();
        $this->assertEquals($expectedRequest, $builder->build($query, $originalRequest));
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            'no fields' => [
                'select' => '',
                'originalRequest' => ['key' => 'value'],
                'expectedRequest' => ['key' => 'value', 'fields' => ''],
            ],
            'two fields' => [
                'select' => 'test1,test2',
                'originalRequest' => ['key' => 'value'],
                'expectedRequest' => ['key' => 'value', 'fields' => 'test1,test2'],
            ]
        ];
    }
}
