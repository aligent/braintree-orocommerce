<?php

namespace Oroo\Bundle\OrganizationConfigCRMProBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\OrganizationConfigCRMProBundle\DependencyInjection\OroOrganizationConfigCRMProExtension;

class OroOrganizationConfigCRMProExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var OroOrganizationConfigCRMProExtension */
    private $extension;

    /** @var ContainerBuilder */
    private $containerBuilder;

    protected function setUp()
    {
        $this->containerBuilder = new ContainerBuilder();
        $this->extension = new OroOrganizationConfigCRMProExtension();
    }

    public function testLoad()
    {
        $this->extension->load([], $this->containerBuilder);
    }
}
