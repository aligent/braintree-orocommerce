<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Tests\Unit\Provider;

use Oro\Bundle\ElasticSearchBundle\Engine\MappingValidator;
use Oro\Bundle\WebsiteElasticSearchBundle\Provider\WebsiteElasticSearchMappingProvider;
use Oro\Bundle\WebsiteSearchBundle\Loader\ConfigurationLoaderInterface;
use Oro\Bundle\WebsiteSearchBundle\Tests\Unit\Provider\WebsiteSearchMappingProviderTest;

class WebsiteElasticSearchMappingProviderTest extends WebsiteSearchMappingProviderTest
{
    /** @var array */
    protected $testMapping = [
        'Oro\TestBundle\Entity\TestEntity' => [
            'alias'  => 'test_entity',
            'fields' => [
                [
                    'name' => 'firstname',
                    'type' => 'text',
                ],
                [
                    'name' => 'qty',
                    'type' => 'integer'
                ]
            ]
        ]
    ];

    /** @var MappingValidator|\PHPUnit_Framework_MockObject_MockObject */
    protected $mappingValidator;

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {
        $this->mappingConfigurationLoader = $this->createMock(ConfigurationLoaderInterface::class);
        $this->mappingConfigurationLoader
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($this->testMapping);

        $this->mappingValidator = $this->createMock(MappingValidator::class);

        return new WebsiteElasticSearchMappingProvider($this->mappingConfigurationLoader, $this->mappingValidator);
    }

    public function testCollectMappingForValidate()
    {
        $this->mappingConfigurationLoader = $this->createMock(ConfigurationLoaderInterface::class);

        $mappings = $this->testMapping;
        $mappings['Oro\TestBundle\Entity\SecondTestEntity'] = [
            'alias'  => 'second_test_entity',
            'fields' => [
                [
                    'name' => 'firstname',
                    'type' => 'text',
                ],
                [
                    'name' => 'qty',
                    'type' => 'text'
                ]
            ]
        ];

        $this->mappingConfigurationLoader
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($mappings);

        $this->mappingValidator = $this->createMock(MappingValidator::class);

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
        ];

        $this->mappingValidator->expects($this->once())
            ->method('validateMappings')
            ->with($expectedMapping);

        $provider = new WebsiteElasticSearchMappingProvider($this->mappingConfigurationLoader, $this->mappingValidator);
        $provider->getMappingConfig();
    }
}
