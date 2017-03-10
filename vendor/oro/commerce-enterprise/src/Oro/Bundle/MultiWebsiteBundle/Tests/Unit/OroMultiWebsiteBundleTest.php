<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit;

use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\Compiler\WebsiteMatcherPass;
use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\OroMultiWebsiteExtension;
use Oro\Bundle\MultiWebsiteBundle\OroMultiWebsiteBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroMultiWebsiteBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExtension()
    {
        $bundle = new OroMultiWebsiteBundle();
        $this->assertInstanceOf(OroMultiWebsiteExtension::class, $bundle->getContainerExtension());
    }

    public function testBuild()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->exactly(2))
            ->method('addCompilerPass')
            ->withConsecutive(
                [$this->isInstanceOf(OverrideServiceCompilerPass::class)],
                [$this->isInstanceOf(WebsiteMatcherPass::class)]
            )
            ->willReturn($this->returnSelf());

        $bundle = new OroMultiWebsiteBundle();
        $bundle->build($container);
    }
}
