<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\OverrideInventoryImportCompilerPass;

class OverrideInventoryImportCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessSkip()
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerMock */
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $containerMock->expects($this->at(0))
            ->method('hasDefinition')
            ->with(
                $this->equalTo('oro_inventory.importexport.strategy_helper.inventory_statuses')
            )
            ->will($this->returnValue(false));

        $containerMock
            ->expects($this->never())
            ->method('getDefinition');

        $compilerPass = new OverrideInventoryImportCompilerPass();
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
            ->with('setSuccessor', [new Reference('oro_warehouse.importexport.strategy_helper.warehouse')]);

        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerMock */
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $containerMock->expects($this->at(0))
            ->method('hasDefinition')
            ->with(
                $this->equalTo('oro_inventory.importexport.strategy_helper.inventory_statuses')
            )
            ->willReturn(true);

        $containerMock->expects($this->at(1))
            ->method('hasDefinition')
            ->with(
                $this->equalTo('oro_warehouse.importexport.strategy_helper.warehouse')
            )
            ->willReturn(true);

        $containerMock->expects($this->exactly(1))
            ->method('getDefinition')
            ->with(
                $this->equalTo('oro_inventory.importexport.strategy_helper.inventory_statuses')
            )
            ->willReturn($definition);

        $compilerPass = new OverrideInventoryImportCompilerPass();
        $compilerPass->process($containerMock);
    }
}
