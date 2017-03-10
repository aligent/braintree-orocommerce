<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit;

use Oro\Bundle\ElasticSearchBundle\OroElasticSearchBundle;

class OroElasticSearchBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $bundle = new OroElasticSearchBundle();

        $container->expects($this->at(0))
            ->method('addCompilerPass')
            ->with(
                $this->isInstanceOf(
                    'Oro\Bundle\ElasticSearchBundle\DependencyInjection\Compiler\ElasticSearchProviderPass'
                )
            );

        $bundle->build($container);
    }
}
