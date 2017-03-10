<?php

namespace Oro\Bundle\FusionChartsBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FusionChartsBundle\DependencyInjection\OroFusionChartsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroFusionChartsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroFusionChartsExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new OroFusionChartsExtension();
    }

    public function testLoad()
    {
        $this->extension->load(array(), $this->container);
    }
}
