<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\EventListener;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Oro\Component\Testing\Unit\FormViewListenerTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\PricingBundle\Entity\PriceListCustomerGroupFallback;
use Oro\Bundle\PricingBundle\Entity\PriceListToCustomerGroup;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use Oro\Bundle\PricingBundle\Entity\PriceListCustomerFallback;
use Oro\Bundle\WebsiteBundle\Entity\Website;

use Oro\Bundle\MultiWebsiteBundle\EventListener\CustomerGroupFormViewListener;

class CustomerGroupFormViewListenerTest extends FormViewListenerTestCase
{
    use EntityTrait;

    /**
     * @var WebsiteProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->websiteProvider = $this->createMock('Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface');
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->doctrineHelper, $this->translator, $this->websiteProvider);
    }

    public function testOnViewNoRequest()
    {
        /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestStack */
        $requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');

        $listener = $this->getListener($requestStack);
        $this->doctrineHelper->expects($this->never())
            ->method('getEntityReference');

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Twig_Environment $env */
        $env = $this->createMock('\Twig_Environment');
        $event = $this->createEvent($env);
        $listener->onCustomerGroupView($event);
    }

    public function testOnCustomerGroupView()
    {
        $customerGroupId = 1;
        $customerGroup = new CustomerGroup();
        $websiteId1 = 12;
        $websiteId2 = 13;
        $websiteId3 = 14;

        /** @var Website $website1 */
        $website1 = $this->getEntity('Oro\Bundle\WebsiteBundle\Entity\Website', ['id' => $websiteId1]);
        /** @var Website $website2 */
        $website2 = $this->getEntity('Oro\Bundle\WebsiteBundle\Entity\Website', ['id' => $websiteId2]);
        /** @var Website $website3 */
        $website3 = $this->getEntity('Oro\Bundle\WebsiteBundle\Entity\Website', ['id' => $websiteId3]);

        $websites = [$website1, $website2, $website3];

        $priceListToCustomerGroup1 = new PriceListToCustomerGroup();
        $priceListToCustomerGroup1->setCustomerGroup($customerGroup);
        $priceListToCustomerGroup1->setWebsite($website1);
        $priceListToCustomerGroup1->setPriority(3);
        $priceListToCustomerGroup2 = clone $priceListToCustomerGroup1;
        $priceListToCustomerGroup2->setWebsite($website2);
        $priceListsToCustomerGroup = [$priceListToCustomerGroup1, $priceListToCustomerGroup2];

        $templateHtml = 'template_html';

        $fallbackEntity = new PriceListCustomerGroupFallback();
        $fallbackEntity->setCustomerGroup($customerGroup);
        $fallbackEntity->setWebsite($website3);
        $fallbackEntity->setFallback(PriceListCustomerFallback::CURRENT_ACCOUNT_ONLY);

        $request = new Request(['id' => $customerGroupId]);
        $requestStack = $this->getRequestStack($request);

        /** @var CustomerGroupFormViewListener $listener */
        $listener = $this->getListener($requestStack);

        $this->setRepositoryExpectationsForCustomerGroup(
            $websites,
            $customerGroup,
            $priceListsToCustomerGroup,
            $fallbackEntity
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Twig_Environment $environment */
        $environment = $this->createMock('\Twig_Environment');
        $environment->expects($this->once())
            ->method('render')
            ->with(
                'OroMultiWebsiteBundle:Customer:price_list_view.html.twig',
                [
                    'priceListsByWebsites' => [
                        $websiteId1 => [$priceListToCustomerGroup1],
                        $websiteId2 => [$priceListToCustomerGroup2],
                    ],
                    'fallbackByWebsites' => [
                        $websiteId3 => PriceListCustomerGroupFallback::CURRENT_ACCOUNT_GROUP_ONLY,
                    ],
                    'websites' => [$website1, $website2, $website3],
                    'choices' => [
                        'oro.pricing.fallback.website.label',
                        'oro.pricing.fallback.current_customer_group_only.label',
                    ],
                ]
            )
            ->willReturn($templateHtml);

        $event = $this->createEvent($environment);
        $listener->onCustomerGroupView($event);
        $scrollData = $event->getScrollData()->getData();

        $this->assertEquals(
            [$templateHtml],
            $scrollData[ScrollData::DATA_BLOCKS][1][ScrollData::SUB_BLOCKS][0][ScrollData::DATA]
        );
    }

    public function testOnEntityEdit()
    {
        $formView = new FormView();
        $templateHtml = 'template_html';

        /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestStack */
        $requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');

        /** @var CustomerGroupFormViewListener $listener */
        $listener = $this->getListener($requestStack);

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Twig_Environment $environment */
        $environment = $this->createMock('\Twig_Environment');

        $environment->expects($this->once())
            ->method('render')
            ->with('OroMultiWebsiteBundle:Customer:price_list_update.html.twig', ['form' => $formView])
            ->willReturn($templateHtml);

        $event = $this->createEvent($environment, $formView);
        $listener->onEntityEdit($event);
        $scrollData = $event->getScrollData()->getData();

        $this->assertEquals(
            [$templateHtml],
            $scrollData[ScrollData::DATA_BLOCKS][1][ScrollData::SUB_BLOCKS][0][ScrollData::DATA]
        );
    }


    /**
     * @param Website[] $websites
     * @param CustomerGroup $customerGroup
     * @param PriceListToCustomerGroup[] $priceListsToCustomerGroup
     * @param PriceListCustomerGroupFallback $fallbackEntity
     */
    protected function setRepositoryExpectationsForCustomerGroup(
        $websites,
        CustomerGroup $customerGroup,
        $priceListsToCustomerGroup,
        PriceListCustomerGroupFallback $fallbackEntity
    ) {
        $this->websiteProvider->expects($this->once())
            ->method('getWebsites')
            ->willReturn($websites);
        
        $priceToCustomerGroupRepository = $this
            ->getMockBuilder('Oro\Bundle\PricingBundle\Entity\Repository\PriceListToCustomerGroupRepository')
            ->disableOriginalConstructor()
            ->getMock();
        
        $priceToCustomerGroupRepository->expects($this->once())
            ->method('findBy')
            ->with(['customerGroup' => $customerGroup])
            ->willReturn($priceListsToCustomerGroup);
        
        $fallbackRepository = $this
            ->getMockBuilder('Oro\Bundle\PricingBundle\Entity\Repository\PriceListToCustomerGroupRepository')
            ->disableOriginalConstructor()
            ->getMock();
        
        $fallbackRepository->expects($this->once())
            ->method('findBy')
            ->with(['customerGroup' => $customerGroup])
            ->willReturn([$fallbackEntity]);
        
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityReference')
            ->willReturn($customerGroup);
        
        $this->doctrineHelper->expects($this->exactly(2))
            ->method('getEntityRepository')
            ->will(
                $this->returnValueMap(
                    [
                        ['OroPricingBundle:PriceListToCustomerGroup', $priceToCustomerGroupRepository],
                        ['OroPricingBundle:PriceListCustomerGroupFallback', $fallbackRepository]
                    ]
                )
            );
    }


    /**
     * @param array $scrollData
     * @param string $html
     */
    protected function assertScrollDataPriceBlock(array $scrollData, $html)
    {
        $this->assertEquals(
            'oro.pricing.productprice.entity_plural_label.trans',
            $scrollData[ScrollData::DATA_BLOCKS][1][ScrollData::TITLE]
        );
        $this->assertEquals(
            [$html],
            $scrollData[ScrollData::DATA_BLOCKS][1][ScrollData::SUB_BLOCKS][0][ScrollData::DATA]
        );
    }

    /**
     * @param \Twig_Environment $environment
     * @param FormView $formView
     * @return BeforeListRenderEvent
     */
    protected function createEvent(\Twig_Environment $environment, FormView $formView = null)
    {
        $defaultData = [
            ScrollData::DATA_BLOCKS => [
                [
                    ScrollData::SUB_BLOCKS => [
                        [
                            ScrollData::DATA => [],
                        ]
                    ]
                ]
            ]
        ];

        return new BeforeListRenderEvent($environment, new ScrollData($defaultData), $formView);
    }

    /**
     * @param RequestStack $requestStack
     * @return CustomerGroupFormViewListener
     */
    protected function getListener(RequestStack $requestStack)
    {
        return new CustomerGroupFormViewListener(
            $requestStack,
            $this->translator,
            $this->doctrineHelper,
            $this->websiteProvider
        );
    }

    /**
     * @param $request
     * @return \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    protected function getRequestStack($request)
    {
        /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestStack */
        $requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
        $requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);

        return $requestStack;
    }
}
