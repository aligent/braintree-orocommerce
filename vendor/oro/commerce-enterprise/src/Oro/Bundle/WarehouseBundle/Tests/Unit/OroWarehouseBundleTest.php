<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Unit;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\OverrideInventoryDataConverterCompilerPass;
use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\OverrideInventoryImportCompilerPass;
use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\OverrideInventoryTemplateFixtureCompilerPass;
use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Oro\Bundle\WarehouseBundle\OroWarehouseBundle;

class OroWarehouseBundleTest extends \PHPUnit_Framework_TestCase
{
    /** @var OroWarehouseBundle */
    protected $bundle;

    protected function setUp()
    {
        $this->bundle = new OroWarehouseBundle();
    }

    public function testBuild()
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerBuilder->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(OverrideServiceCompilerPass::class));
        $containerBuilder->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(OverrideInventoryImportCompilerPass::class));
        $containerBuilder->expects($this->at(2))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(OverrideInventoryTemplateFixtureCompilerPass::class));
        $containerBuilder->expects($this->at(3))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(OverrideInventoryDataConverterCompilerPass::class));

        $this->bundle->build($containerBuilder);
    }
}
