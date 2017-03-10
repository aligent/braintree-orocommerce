<?php

namespace Oro\Bundle\EwsBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\EwsBundle\DependencyInjection\OroEwsExtension;
use Oro\Bundle\EwsBundle\OroEwsBundle;

class OroEwsExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $extension = new OroEwsExtension();

        $configs = array(
            array('wsdl_endpoint' => '@OroEwsBundle/test')
        );
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->any())
            ->method('getParameter')
            ->with('kernel.bundles')
            ->will($this->returnValue(array('OroEwsBundle' => 'Oro\Bundle\EwsBundle\OroEwsBundle')));

        $isCalled = false;
        $wsdlEndpointPath = '';

        $container->expects($this->any())
            ->method('setParameter')
            ->will(
                $this->returnCallback(
                    function ($name, $value) use (&$isCalled, &$wsdlEndpointPath) {
                        if ($name == 'oro_ews.wsdl_endpoint' && is_string($value)) {
                            $isCalled = true;
                            $wsdlEndpointPath = $value;
                        }
                    }
                )
            );

        $extension->load($configs, $container);

        $this->assertTrue($isCalled);
        $this->assertEquals(
            (new OroEwsBundle())->getPath() . DIRECTORY_SEPARATOR . 'test',
            $wsdlEndpointPath
        );
    }
}
