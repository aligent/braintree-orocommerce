<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use Oro\Bundle\MultiWebsiteBundle\Provider\WebsiteProvider;
use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Oro\Bundle\MultiWebsiteBundle\EventListener\PriceListFormViewListener;
use Oro\Bundle\MultiWebsiteBundle\EventListener\CustomerFormViewListener;
use Oro\Bundle\MultiWebsiteBundle\EventListener\CustomerGroupFormViewListener;

class OverrideServiceCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    const SERVICE_OVERRIDES_COUNT = 4;
    const ARGUMENT_ADD_OVERRIDES_COUNT = 2;

    public function testProcessSkip()
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerMock */
        $containerMock = $this->getMockBuilder(ContainerBuilder::class)
            ->getMock();

        $containerMock->expects($this->exactly(static::SERVICE_OVERRIDES_COUNT))
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_pricing.event_listener.price_list_form_view'),
                    $this->equalTo('oro_website.website.provider'),
                    $this->equalTo('oro_pricing.event_listener.customer_form_view'),
                    $this->equalTo('oro_pricing.event_listener.customer_group_form_view')
                )
            )
            ->will($this->returnValue(false));

        $containerMock
            ->expects($this->never())
            ->method('getDefinition');

        $compilerPass = new OverrideServiceCompilerPass();
        $compilerPass->process($containerMock);
    }

    public function testProcess()
    {
        $definition = $this->getMockBuilder(Definition::class)
            ->setMethods([])
            ->getMock();

        $definition
            ->expects($this->exactly(static::SERVICE_OVERRIDES_COUNT))
            ->method('setClass')
            ->with(
                $this->logicalOr(
                    $this->equalTo(PriceListFormViewListener::class),
                    $this->equalTo(WebsiteProvider::class),
                    $this->equalTo(CustomerFormViewListener::class),
                    $this->equalTo(CustomerGroupFormViewListener::class)
                )
            )
            ->will($this->returnSelf());

        $definition
            ->expects($this->exactly(static::ARGUMENT_ADD_OVERRIDES_COUNT))
            ->method('addArgument');

        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerMock */
        $containerMock = $this->getMockBuilder(ContainerBuilder::class)
            ->getMock();

        $containerMock->expects($this->exactly(static::SERVICE_OVERRIDES_COUNT))
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_pricing.event_listener.price_list_form_view'),
                    $this->equalTo('oro_website.website.provider'),
                    $this->equalTo('oro_pricing.event_listener.customer_form_view'),
                    $this->equalTo('oro_pricing.event_listener.customer_group_form_view')
                )
            )
            ->will($this->returnValue(true));

        $containerMock->expects($this->exactly(static::SERVICE_OVERRIDES_COUNT))
            ->method('getDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_pricing.event_listener.price_list_form_view'),
                    $this->equalTo('oro_website.website.provider'),
                    $this->equalTo('oro_pricing.event_listener.customer_form_view'),
                    $this->equalTo('oro_pricing.event_listener.customer_group_form_view')
                )
            )
            ->will($this->returnValue($definition));

        $compilerPass = new OverrideServiceCompilerPass();
        $compilerPass->process($containerMock);
    }
}
