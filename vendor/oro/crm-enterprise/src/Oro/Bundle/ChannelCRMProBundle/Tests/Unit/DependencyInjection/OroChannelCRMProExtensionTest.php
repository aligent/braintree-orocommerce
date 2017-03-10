<?php

namespace Oro\Bundle\ChannelCRMProBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\ChannelCRMProBundle\DependencyInjection\OroChannelCRMProExtension;

class OroChannelCRMProExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroChannelCRMProExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new OroChannelCRMProExtension();
    }

    public function testLoad()
    {
        $this->extension->load([], $this->container);
    }
}
