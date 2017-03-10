<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\Engine;

use Oro\Bundle\ElasticSearchBundle\Engine\MappingValidator;

class MappingValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  MappingValidator */
    protected $mappingValidator;

    protected function setUp()
    {
        $this->mappingValidator = new MappingValidator();
    }

    /**
     * @param array $mappings
     * @param string|null $expectedExceptionText
     *
     * @dataProvider validateMappingsProvider
     */
    public function testValidateMappings(array $mappings, $expectedExceptionText = null)
    {
        if ($expectedExceptionText) {
            $this->expectException('LogicException');
            $this->expectExceptionMessage($expectedExceptionText);
        }

        $this->mappingValidator->validateMappings($mappings);
    }

    /**
     * @return array
     */
    public function validateMappingsProvider()
    {
        return [
            'empty mapping' => [
                'mappings' => []
            ],
            'correct mappings' => [
                'mappings' => [
                    [
                        'name' => 'sku',
                        'type' => 'string',
                        'entity' => 'ProductEntity'
                    ],
                    [
                        'name' => 'product',
                        'type' => 'integer',
                        'entity' => 'ProductEntity',
                    ],
                    [
                        'name' => 'product',
                        'type' => 'integer',
                        'entity' => 'AnotherProductEntity',
                    ]
                ]
            ],
            'incorrect mapping' => [
                'mappings' => [
                    [
                        'name' => 'sku',
                        'type' => 'string',
                        'entity' => 'ProductEntity'
                    ],
                    [
                        'name' => 'product',
                        'type' => 'integer',
                        'entity' => 'ProductEntity',
                    ],
                    [
                        'name' => 'product',
                        'type' => 'text',
                        'entity' => 'AnotherProductEntity',
                    ]
                ],
                'expectedExceptionText' =>
                    'Field "product" in entity "ProductEntity" has type "integer", ' .
                    'but same field in entity "AnotherProductEntity" has type "text"'
            ],
            'incorrect mapping with more than 2 errors' => [
                'mappings' => [
                    [
                        'name' => 'sku',
                        'type' => 'string',
                        'entity' => 'ProductEntity'
                    ],
                    [
                        'name' => 'product',
                        'type' => 'integer',
                        'entity' => 'ProductEntity',
                    ],
                    [
                        'name' => 'product',
                        'type' => 'text',
                        'entity' => 'AnotherProductEntity',
                    ],
                    [
                        'name' => 'product',
                        'type' => 'decimal',
                        'entity' => 'OneMoreAnotherProductEntity',
                    ]
                ],
                'expectedExceptionText' =>
                    'Field "product" in entity "ProductEntity" has type "integer", ' .
                    'but same field in entity "AnotherProductEntity" has type "text"'
            ]
        ];
    }
}
