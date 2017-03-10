<?php

namespace Oro\Bridge\MarketingCRMPro\Tests\Unit\Model\Data\Transformer;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\ChartBundle\Model\Data\ArrayData;
use Oro\Bundle\ChartBundle\Model\Data\MappedData;
use Oro\Bridge\MarketingCRMPro\Model\Data\Transformer\CampaignMultiLineDataTransformer;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;

class CampaignMultiLineDataTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CampaignMultiLineDataTransformer
     */
    protected $transformer;

    /**
     * @var FeatureChecker | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $featureChecker;

    protected function setUp()
    {
        $this->transformer = new CampaignMultiLineDataTransformer();
        $this->featureChecker = $this->getMockBuilder(FeatureChecker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transformer->setFeatureChecker($this->featureChecker);
        $this->transformer->addFeature('campaign');
    }

    /**
     * @param array $data
     * @param array $chartOptions
     * @param array $expected
     *
     * @dataProvider dataProvider
     */
    public function testTransform(array $data, array $chartOptions, array $expected)
    {
        $sourceData = new ArrayData($data);

        $mapping = [
            'label' => 'label',
            'value' => 'value',
        ];

        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with($this->anything())
            ->willReturn(true);

        $result = $this->transformer->transform(
            new MappedData($mapping, $sourceData),
            $chartOptions
        );

        $this->assertEquals($expected, $result->toArray());
    }

    public function testTransformWhenFeatureIsDisabled()
    {
        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with($this->anything())
            ->willReturn(false);

        $result = $this->transformer->transform(
            new MappedData([], new ArrayData([])),
            []
        );

        $this->assertEquals(new ArrayData([]), $result);
    }

    public function testEmptyData()
    {
        $sourceData = new ArrayData([]);
        $data = new MappedData([], $sourceData);
        $chartOptions = [
            'data_schema' => [
                'label' => [
                    'field_name' => 'label'
                ],
                'value' => [
                    'field_name' => 'value'
                ]
            ],
            'default_settings' => [
                'groupingOption' => 'option',
                'period' => Campaign::PERIOD_DAILY
            ]
        ];

        $result = $this->transformer->transform($data, $chartOptions);
        $this->assertEquals($sourceData, $result);
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProvider()
    {
        return [
            'fill_labels' => [
                [
                    [
                        'option' => 'o1',
                        'label'  => '2014-07-07',
                        'value'  => 1,
                    ],
                    [
                        'option' => 'o2',
                        'label'  => '2014-07-09',
                        'value'  => 1,
                    ]
                ],
                [
                    'data_schema'      => [
                        'label' => [
                            'field_name' => 'label'
                        ],
                        'value' => [
                            'field_name' => 'value'
                        ]
                    ],
                    'default_settings' => [
                        'groupingOption' => 'option',
                        'period'         => Campaign::PERIOD_DAILY
                    ]
                ],
                [
                    'categories' => [
                        'category' => [
                            ['label' => '2014-07-06'],
                            ['label' => '2014-07-07'],
                            ['label' => '2014-07-08'],
                            ['label' => '2014-07-09']
                        ]
                    ],
                    'dataset'    => [
                        [
                            'seriesname' => 'o1',
                            'data'       => [
                                ['value' => 0],
                                ['value' => 1],
                                ['value' => 0],
                                ['value' => 0],
                            ]
                        ],
                        [
                            'seriesname' => 'o2',
                            'data'       => [
                                ['value' => 0],
                                ['value' => 0],
                                ['value' => 0],
                                ['value' => 1],
                            ]
                        ]
                    ]
                ]
            ],
            'skip_labels' => [
                [
                    [
                        'option' => 'o1',
                        'label'  => '2014-07-07',
                        'value'  => 1,
                    ],
                    [
                        'option' => 'o2',
                        'label'  => '2014-07-09',
                        'value'  => 1,
                    ]
                ],
                [
                    'data_schema'      => [
                        'label' => [
                            'field_name' => 'label'
                        ],
                        'value' => [
                            'field_name' => 'value'
                        ]
                    ],
                    'default_settings' => [
                        'groupingOption' => 'option',
                        'period'         => Campaign::PERIOD_HOURLY
                    ]
                ],
                [
                    'categories' => [
                        'category' => [
                            ['label' => '2014-07-07'],
                            ['label' => '2014-07-09']
                        ]
                    ],
                    'dataset'    => [
                        [
                            'seriesname' => 'o1',
                            'data'       => [
                                ['value' => 1],
                                ['value' => 0],
                            ]
                        ],
                        [
                            'seriesname' => 'o2',
                            'data'       => [
                                ['value' => 0],
                                ['value' => 1],
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
