<?php

namespace Oro\Bundle\CustomerProBundle\Tests\Unit;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\CustomerProBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Oro\Bundle\CustomerProBundle\DependencyInjection\OroCustomerProExtension;
use Oro\Bundle\CustomerProBundle\OroCustomerProBundle;

class OroCustomerProBundleTest extends \PHPUnit_Framework_TestCase
{
    /** @var OroCustomerProBundle */
    protected $bundle;

    protected function setUp()
    {
        $this->bundle = new OroCustomerProBundle();
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

        $this->bundle->build($containerBuilder);
    }

    public function testGetContainerExtension()
    {
        $this->assertInstanceOf(OroCustomerProExtension::class, $this->bundle->getContainerExtension());
    }
}
