<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\ElasticSearchBundle\Engine\ElasticSearch;

abstract class AbstractElasticSearchProviderPassTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_HOST      = '127.0.0.1';
    const DEFAULT_PORT      = '9200';
    const DEFAULT_USERNAME  = 'username';
    const DEFAULT_PASSWORD  = '1234567';
    const DEFAULT_INDEX_NAME  = 'oro_test_index';

    /**
     * @var CompilerPassInterface
     */
    protected $compiler;

    /**
     * @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    public function setUp()
    {
        $this->container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    abstract protected function getEngineParametersKey();

    public function testProcessWhenEngineIsNotElasticSearch()
    {
        $this->container
            ->expects($this->once())
            ->method('getParameter')
            ->with('search_engine_name')
            ->willReturn('Orm');

        $this->container
            ->expects($this->never())
            ->method('setParameter');

        $this->compiler->process($this->container);
    }

    /**
     * @dataProvider incorrectIndexParameterProvider
     * @param array $parameters
     * @param string $exceptionMessage
     */
    public function testProcessWithIncorrectIndexParameter(array $parameters, $exceptionMessage)
    {
        $this->container
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                ['search_engine_name'],
                [$this->getEngineParametersKey()]
            )
            ->willReturnOnConsecutiveCalls(ElasticSearch::ENGINE_NAME, $parameters);

        $this->container
            ->expects($this->never())
            ->method('setParameter');

        $this->expectException('\RuntimeException');
        $this->expectExceptionMessage($exceptionMessage);

        $this->compiler->process($this->container);
    }

    /**
     * @return array
     */
    abstract public function incorrectIndexParameterProvider();

    /**
     * @dataProvider processProvider
     * @param array $hasParameters
     * @param array $getParameters
     * @param array $expectedSearchConfiguration
     */
    public function testProcess(array $hasParameters, array $getParameters, array $expectedSearchConfiguration)
    {
        $this->configureConsecutiveMock('hasParameter', $hasParameters);
        $this->configureConsecutiveMock('getParameter', $getParameters);

        $this->container
            ->expects($this->once())
            ->method('setParameter')
            ->with($this->getEngineParametersKey(), $expectedSearchConfiguration);

        $this->compiler->process($this->container);
    }

    /**
     * @return array
     */
    abstract public function processProvider();

    /**
     * @param string $method
     * @param array $parameters
     */
    protected function configureConsecutiveMock($method, array $parameters)
    {
        // TODO: Could be refactored after upgrade to php 5.6 by using argument unpacking
        $withConsecutive = array_map(function ($value) {
            return [$value];
        }, array_keys($parameters));

        $invocationMockBuilder = $this->container
            ->expects($this->exactly(count($parameters)))
            ->method($method);

        call_user_func_array([$invocationMockBuilder, 'withConsecutive'], $withConsecutive);
        call_user_func_array([$invocationMockBuilder, 'willReturnOnConsecutiveCalls'], array_values($parameters));
    }
}
