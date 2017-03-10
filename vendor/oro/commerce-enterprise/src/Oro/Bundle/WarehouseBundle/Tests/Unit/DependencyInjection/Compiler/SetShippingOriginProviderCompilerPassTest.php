<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\SetShippingOriginProviderCompilerPass;

class SetShippingOriginProviderCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessSkip()
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerMock */
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $containerMock->expects($this->at(0))
            ->method('hasDefinition')
            ->with(
                $this->equalTo('oro_warehouse.warehouse_address.provider')
            )
            ->will($this->returnValue(false));

        $containerMock
            ->expects($this->never())
            ->method('getDefinition');

        $compilerPass = new SetShippingOriginProviderCompilerPass();
        $compilerPass->process($containerMock);
    }

    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->setMethods([])
            ->getMock();

        $definition
            ->expects($this->once())
            ->method('addMethodCall')
            ->with('setShippingOriginProvider', [new Reference('oro_shipping.shipping_origin.provider')]);

        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerMock */
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $containerMock->expects($this->at(0))
            ->method('hasDefinition')
            ->with(
                $this->equalTo('oro_warehouse.warehouse_address.provider')
            )
            ->willReturn(true);

        $containerMock->expects($this->at(1))
            ->method('hasDefinition')
            ->with(
                $this->equalTo('oro_shipping.shipping_origin.provider')
            )
            ->willReturn(true);

        $containerMock->expects($this->exactly(1))
            ->method('getDefinition')
            ->with(
                $this->equalTo('oro_warehouse.warehouse_address.provider')
            )
            ->willReturn($definition);

        $compilerPass = new SetShippingOriginProviderCompilerPass();
        $compilerPass->process($containerMock);
    }
}
