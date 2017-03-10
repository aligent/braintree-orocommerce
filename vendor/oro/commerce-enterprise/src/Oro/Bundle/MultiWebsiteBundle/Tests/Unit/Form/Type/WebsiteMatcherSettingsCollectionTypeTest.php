<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteMatcherSettingsCollectionType;
use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteMatcherSettingsType;
use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherInterface;
use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherRegistry;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Validator\Validation;

class WebsiteMatcherSettingsCollectionTypeTest extends FormIntegrationTestCase
{
    /**
     * @var WebsiteMatcherRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $matcherRegistry;

    /**
     * @var WebsiteMatcherSettingsCollectionType
     */
    protected $formType;

    protected function setUp()
    {
        $this->matcherRegistry = $this->getMockBuilder(WebsiteMatcherRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formType = new WebsiteMatcherSettingsCollectionType($this->matcherRegistry);
        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension([
                CollectionType::class => new CollectionType(),
                WebsiteMatcherSettingsType::class => new WebsiteMatcherSettingsType(),
            ], []),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    public function testSubmitEmptyConfig()
    {
        $matcher1 = $this->createMock(WebsiteMatcherInterface::class);
        $matcher1->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label1');
        $matcher1->expects($this->once())
            ->method('getTooltip')
            ->willReturn('Tooltip1');
        $matcher1->expects($this->once())
            ->method('getPriority')
            ->willReturn(1);

        $matcher2 = $this->createMock(WebsiteMatcherInterface::class);
        $matcher2->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label2');
        $matcher2->expects($this->once())
            ->method('getPriority')
            ->willReturn(2);

        $this->matcherRegistry->expects($this->once())
            ->method('getRegisteredMatchers')
            ->willReturn(
                [
                    'matcher1' => $matcher1,
                    'matcher2' => $matcher2
                ]
            );

        $form = $this->factory->create($this->formType, []);
        $expected = [
            [
                'matcher_alias' => 'matcher2',
                'priority' => 2,
                'label' => 'Label2',
                'enabled' => true,
                'tooltip' => null
            ],
            [
                'matcher_alias' => 'matcher1',
                'priority' => 1,
                'label' => 'Label1',
                'enabled' => true,
                'tooltip' => 'Tooltip1'
            ],
        ];
        $this->assertEquals($expected, $form->getData());
    }

    public function testSubmitExistingConfig()
    {
        $existingConfig = [
            [
                'matcher_alias' => 'matcher1',
                'priority' => 100,
                'label' => 'Label1',
                'enabled' => false
            ],
        ];

        $matcher1 = $this->createMock(WebsiteMatcherInterface::class);
        $matcher1->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label1');
        $matcher1->expects($this->once())
            ->method('getTooltip')
            ->willReturn('Tooltip1');
        $matcher1->expects($this->once())
            ->method('getPriority')
            ->willReturn(1);

        $matcher2 = $this->createMock(WebsiteMatcherInterface::class);
        $matcher2->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label2');
        $matcher2->expects($this->once())
            ->method('getPriority')
            ->willReturn(2);

        $this->matcherRegistry->expects($this->once())
            ->method('getRegisteredMatchers')
            ->willReturn(
                [
                    'matcher1' => $matcher1,
                    'matcher2' => $matcher2
                ]
            );

        $form = $this->factory->create($this->formType, $existingConfig);
        $expected = [
            [
                'matcher_alias' => 'matcher1',
                'priority' => 100,
                'label' => 'Label1',
                'enabled' => false,
                'tooltip' => 'Tooltip1'
            ],
            [
                'matcher_alias' => 'matcher2',
                'priority' => 2,
                'label' => 'Label2',
                'enabled' => true,
                'tooltip' => null
            ],
        ];
        $this->assertEquals($expected, $form->getData());
    }

    public function testSubmitExistingConfigAndSubmit()
    {
        $existingConfig = [
            [
                'matcher_alias' => 'matcher1',
                'priority' => 100,
                'label' => 'Label1',
                'enabled' => false
            ],
        ];

        $matcher1 = $this->createMock(WebsiteMatcherInterface::class);
        $matcher1->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label1');
        $matcher1->expects($this->once())
            ->method('getTooltip')
            ->willReturn('Tooltip1');
        $matcher1->expects($this->once())
            ->method('getPriority')
            ->willReturn(1);

        $matcher2 = $this->createMock(WebsiteMatcherInterface::class);
        $matcher2->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label2');
        $matcher2->expects($this->once())
            ->method('getPriority')
            ->willReturn(2);

        $this->matcherRegistry->expects($this->once())
            ->method('getRegisteredMatchers')
            ->willReturn(
                [
                    'matcher1' => $matcher1,
                    'matcher2' => $matcher2
                ]
            );

        $form = $this->factory->create($this->formType, $existingConfig);
        $submittedData = [
            [
                'matcher_alias' => 'matcher1',
                'priority' => 200,
                'enabled' => true
            ],
            [
                'matcher_alias' => 'matcher2',
                'priority' => 2,
                'enabled' => false
            ],
        ];
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $expectedData = [
            [
                'matcher_alias' => 'matcher1',
                'priority' => 200,
                'enabled' => true,
                'label' => 'Label1',
                'tooltip' => 'Tooltip1'
            ],
            [
                'matcher_alias' => 'matcher2',
                'priority' => 2,
                'enabled' => false,
                'label' => 'Label2',
                'tooltip' => null
            ],
        ];
        $this->assertEquals($expectedData, $form->getData());
    }
}
