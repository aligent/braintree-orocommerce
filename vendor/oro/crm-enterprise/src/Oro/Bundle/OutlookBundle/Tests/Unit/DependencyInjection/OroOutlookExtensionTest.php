<?php

namespace Oro\Bundle\OutlookBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;

use Oro\Bundle\OutlookBundle\DependencyInjection\OroOutlookExtension;

class OroOutlookExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $extension = new OroOutlookExtension();
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->once())
            ->method('prependExtensionConfig')
            ->with('oro_outlook', $this->isType('array'));

        $extension->load([], $container);
    }
}
