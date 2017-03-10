<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\EventListener;

use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Oro\Bundle\MultiWebsiteBundle\EventListener\PriceListWebsiteFormViewListener;
use Oro\Component\Testing\Unit\FormViewListenerTestCase;
use Oro\Bundle\PricingBundle\Entity\PriceListWebsiteFallback;

class PriceListWebsiteFormViewListenerTest extends FormViewListenerTestCase
{
    public function testOnWebsiteEdit()
    {
        $renderedHtml = 'rendered_html';
        $event = $this->createEvent($renderedHtml);

        $requestStack = $this->getRequestStack();

        $listener = new PriceListWebsiteFormViewListener(
            $requestStack,
            $this->doctrineHelper,
            $this->translator,
            '\Website',
            '\PriceListToWebsite'
        );

        $listener->onWebsiteEdit($event);
        $scrollData = $event->getScrollData()->getData();
        $this->assertEquals(
            [$renderedHtml],
            $scrollData[ScrollData::DATA_BLOCKS][1][ScrollData::SUB_BLOCKS][0][ScrollData::DATA]
        );
    }

    /**
     * @dataProvider testOnWebsiteViewDataProvider
     *
     * @param PriceListWebsiteFallback|null $fallbackEntity
     * @param string $expectedFallbackValue
     */
    public function testOnWebsiteView($fallbackEntity, $expectedFallbackValue)
    {
        $renderedHtml = $expectedFallbackValue;
        $event = $this->createEvent($renderedHtml);

        $requestStack = $this->getRequestStack();

        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($this->getRequest());

        /** @var \Oro\Bundle\EntityBundle\ORM\DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject $doctrineHelper */
        $doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $fallbackRepository = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $fallbackRepository->expects($this->once())->method('findOneBy')->willReturn($fallbackEntity);

        $manager->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($manager);
        $doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with('OroPricingBundle:PriceListWebsiteFallback')
            ->willReturn($fallbackRepository);
        $listener = new PriceListWebsiteFormViewListener(
            $requestStack,
            $doctrineHelper,
            $this->translator,
            '\Website',
            '\PriceListToWebsite'
        );
        $listener->onWebsiteView($event);

        $this->assertEquals(
            [$renderedHtml],
            $event->getScrollData()->getData()[ScrollData::DATA_BLOCKS][1][ScrollData::SUB_BLOCKS][0][ScrollData::DATA]
        );
        $this->assertEquals(
            'oro.pricing.pricelist.entity_plural_label.trans',
            $event->getScrollData()->getData()[ScrollData::DATA_BLOCKS][1][ScrollData::TITLE]
        );
    }

    /**
     * @return array
     */
    public function testOnWebsiteViewDataProvider()
    {
        return [
            'notExistingFallback' => [
                'fallbackEntity' => null,
                'expectedFallbackValue' => 'oro.pricing.fallback.config.label',
            ],
            'existingDefaultFallback' => [
                'fallbackEntity' => (new PriceListWebsiteFallback())
                    ->setFallback(PriceListWebsiteFallback::CURRENT_WEBSITE_ONLY),
                'expectedFallbackValue' => 'oro.pricing.fallback.current_website_only.label',
            ],
        ];
    }

    public function testOnWebsiteViewWhenRequestIsNull()
    {
        $requestStack = $this->getRequestStack();

        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $listener = new PriceListWebsiteFormViewListener(
            $requestStack,
            $this->doctrineHelper,
            $this->translator,
            '\Website',
            '\PriceListToWebsite'
        );

        $event = $this->getBeforeListRenderEventMock();
        $event->expects($this->never())
            ->method('getScrollData');

        $listener->onWebsiteView($event);
    }

    /**
     * @param string $renderedHtml
     * @return BeforeListRenderEvent
     */
    protected function createEvent($renderedHtml)
    {
        $environment = $this->getEnvironment($renderedHtml);
        $scrollData = $this->getScrollData();

        return new BeforeListRenderEvent($environment, $scrollData);
    }

    /**
     * @return ScrollData
     */
    protected function getScrollData()
    {
        return new ScrollData([
            ScrollData::DATA_BLOCKS => [
                [
                    ScrollData::SUB_BLOCKS => [
                        [
                            ScrollData::DATA => []
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * @param string $renderedTemplate
     * @return \PHPUnit_Framework_MockObject_MockObject|\Twig_Environment
     */
    protected function getEnvironment($renderedTemplate)
    {
        $environment = $this->createMock('\Twig_Environment');

        $environment->expects($this->once())
            ->method('render')
            ->willReturn($renderedTemplate);

        return $environment;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestStack
     */
    protected function getRequestStack()
    {
        return $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
    }
}
