<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\ElasticSearchBundle\DependencyInjection\Compiler\ElasticSearchRequestBuilderWherePass;

class ElasticSearchRequestBuilderWherePassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ElasticSearchRequestBuilderWherePass
     */
    protected $compilerPass;

    /**
     * @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $containerBuilder;

    public function setUp()
    {
        $this->containerBuilder = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->getMock();

        $this->compilerPass = new ElasticSearchRequestBuilderWherePass();
    }

    public function testProcessRegistryDoesNotExist()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(ElasticSearchRequestBuilderWherePass::REGISTRY_SERVICE)
            ->willReturn(false);

        $this->containerBuilder
            ->expects($this->never())
            ->method('getDefinition');

        $this->containerBuilder
            ->expects($this->never())
            ->method('findTaggedServiceIds');

        $this->compilerPass->process($this->containerBuilder);
    }

    public function testProcessNoTaggedServicesFound()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(ElasticSearchRequestBuilderWherePass::REGISTRY_SERVICE)
            ->willReturn(true);

        $this->containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn([]);

        $this->containerBuilder
            ->expects($this->never())
            ->method('getDefinition');

        $this->compilerPass->process($this->containerBuilder);
    }

    public function testProcessWithTaggedServices()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(ElasticSearchRequestBuilderWherePass::REGISTRY_SERVICE)
            ->willReturn(true);

        $registryServiceDefinition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $this->containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with(ElasticSearchRequestBuilderWherePass::REGISTRY_SERVICE)
            ->willReturn($registryServiceDefinition);

        $taggedServices = [
            'service.name.1' => [[]],
            'service.name.2' => [[]],
            'service.name.3' => [[]],
            'service.name.4' => [[]],
        ];

        $this->containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn($taggedServices);

        $registryServiceDefinition
            ->expects($this->exactly(4))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addWherePartBuilder', [new Reference('service.name.1')]],
                ['addWherePartBuilder', [new Reference('service.name.2')]],
                ['addWherePartBuilder', [new Reference('service.name.3')]],
                ['addWherePartBuilder', [new Reference('service.name.4')]]
            );

        $this->compilerPass->process($this->containerBuilder);
    }
}
