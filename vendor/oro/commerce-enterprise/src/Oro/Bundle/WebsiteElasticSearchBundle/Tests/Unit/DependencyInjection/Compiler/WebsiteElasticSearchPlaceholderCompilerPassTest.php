<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\WebsiteElasticSearchBundle\DependencyInjection\Compiler\WebsiteElasticSearchPlaceholderCompilerPass;

class WebsiteElasticSearchPlaceholderCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerBuilder
     */
    private $containerBuilder;

    protected function setUp()
    {
        $this->containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function runCompilerPass()
    {
        $pass = new WebsiteElasticSearchPlaceholderCompilerPass();
        $pass->process($this->containerBuilder);
    }

    public function testProcessWhenPlaceholderRegistryDefinitionNotExists()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('has')
            ->with(WebsiteElasticSearchPlaceholderCompilerPass::WEBSITE_SEARCH_PLACEHOLDER_REGISTRY)
            ->willReturn(false);

        $this->containerBuilder
            ->expects($this->never())
            ->method('findTaggedServiceIds');

        $this->runCompilerPass();
    }

    public function testProcessWhenPlaceholderRegistryDefinitionExists()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('has')
            ->with(WebsiteElasticSearchPlaceholderCompilerPass::WEBSITE_SEARCH_PLACEHOLDER_REGISTRY)
            ->willReturn(true);

        $placeholderRegistryDefinition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with(WebsiteElasticSearchPlaceholderCompilerPass::WEBSITE_SEARCH_PLACEHOLDER_REGISTRY)
            ->willReturn($placeholderRegistryDefinition);

        $services = ['LocalizationIdPlaceholder' => []];

        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with(WebsiteElasticSearchPlaceholderCompilerPass::WEBSITE_SEARCH_PLACEHOLDER_TAG)
            ->willReturn($services);

        $placeholderRegistryDefinition->expects($this->once())
            ->method('addMethodCall')
            ->with('addPlaceholder', [new Reference('LocalizationIdPlaceholder')]);

        $this->runCompilerPass();
    }
}
