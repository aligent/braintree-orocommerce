<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\Provider;

use Oro\Bundle\ElasticSearchBundle\Engine\MappingValidator;
use Oro\Bundle\ElasticSearchBundle\Provider\ElasticSearchMappingProvider;
use Oro\Bundle\SearchBundle\Tests\Unit\Provider\SearchMappingProviderTest;

class ElasticSearchMappingProviderTest extends SearchMappingProviderTest
{
    /**
     * @var ElasticSearchMappingProvider
     */
    protected $provider;

    /**
     * @var MappingValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mappingValidator;

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {
        if (!$this->provider) {
            $mappingValidator = $this->getMappingValidator();
            $this->provider = new ElasticSearchMappingProvider($this->eventDispatcher, $this->cache);
            $this->provider->setMappingConfig($this->testMapping);
            $this->provider->setMappingValidator($mappingValidator);
        }

        return $this->provider;
    }

    /**
     * @return ElasticSearchMappingProvider
     */
    protected function getProviderWithDifferentMapping()
    {

        $mappings = $this->testMapping;
        $mappings['Oro\TestBundle\Entity\SecondTestEntity'] = [
            'alias'  => 'second_test_entity',
            'fields' => [
                [
                    'name' => 'firstname',
                    'target_type' => 'text',
                    'target_columns' => ['firstname']
                ],
                [
                    'name' => 'qty',
                    'target_type' => 'text',
                    'target_columns' => ['qty']
                ]
            ]
        ];

        $mappingValidator = $this->getMappingValidator();
        $provider = new ElasticSearchMappingProvider($this->eventDispatcher, $this->cache, $mappingValidator);
        $provider->setMappingConfig($mappings);

        return $provider;
    }

    public function testCollectMappingForValidate()
    {
        $mappings = $this->testMapping;
        $mappings['Oro\TestBundle\Entity\SecondTestEntity'] = [
            'alias'  => 'second_test_entity',
            'fields' => [
                [
                    'name' => 'firstname',
                    'target_type' => 'text',
                    'target_columns' => ['firstname']
                ],
                [
                    'name' => 'qty',
                    'target_type' => 'text',
                    'target_columns' => ['qty']
                ],
                [
                    'name' => 'group',
                    'relation_type' => 'many-to-one',
                    'relation_fields' => [
                        [
                            'name' => 'name',
                            'target_type' => 'text',
                            'target_fields' => ['group']
                        ]
                    ]
                ]
            ]
        ];

        $mappingValidator = $this->getMappingValidator();
        $provider = new ElasticSearchMappingProvider($this->eventDispatcher, $this->cache);
        $provider->setMappingConfig($mappings);
        $provider->setMappingValidator($mappingValidator);

        $expectedMapping = [
            [
                'name' => 'firstname',
                'type' => 'text',
                'entity' => 'Oro\TestBundle\Entity\TestEntity',
            ],
            [
                'name' => 'qty',
                'type' => 'integer',
                'entity' => 'Oro\TestBundle\Entity\TestEntity',
            ],
            [
                'name' => 'firstname',
                'type' => 'text',
                'entity' => 'Oro\TestBundle\Entity\SecondTestEntity',
            ],
            [
                'name' => 'qty',
                'type' => 'text',
                'entity' => 'Oro\TestBundle\Entity\SecondTestEntity',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'entity' => 'Oro\TestBundle\Entity\SecondTestEntity',
            ],
        ];

        $this->getMappingValidator()->expects($this->once())
            ->method('validateMappings')
            ->with($expectedMapping);

        $provider->getMappingConfig();
    }

    /**
     * @return MappingValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMappingValidator()
    {
        if (!$this->mappingValidator) {
            $this->mappingValidator = $this->createMock(MappingValidator::class);
        }

        return $this->mappingValidator;
    }
}
