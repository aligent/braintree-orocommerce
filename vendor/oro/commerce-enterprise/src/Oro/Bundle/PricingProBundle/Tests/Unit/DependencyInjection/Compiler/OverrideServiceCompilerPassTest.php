<?php

namespace Oro\Bundle\PricingProBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\PricingProBundle\ImportExport\Reader\ProPriceListProductPricesReader;
use Oro\Bundle\PricingProBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class OverrideServiceCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessSkip()
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerMock */
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $containerMock->expects($this->once())
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_pricing.importexport.reader.price_list_product_prices')
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
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->setMethods([])
            ->getMock();

        $definition
            ->expects($this->once())
            ->method('setClass')
            ->with(
                $this->logicalOr(
                    $this->equalTo(ProPriceListProductPricesReader::class)
                )
            )
            ->will($this->returnSelf());

        $definition
            ->expects($this->once())
            ->method('addMethodCall')
            ->with('setSecurityFacade', $this->isType('array'));

        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerMock */
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $containerMock->expects($this->once())
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_pricing.importexport.reader.price_list_product_prices')
                )
            )
            ->will($this->returnValue(true));

        $containerMock->expects($this->once())
            ->method('getDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_pricing.importexport.reader.price_list_product_prices')
                )
            )
            ->will($this->returnValue($definition));

        $compilerPass = new OverrideServiceCompilerPass();
        $compilerPass->process($containerMock);
    }
}
