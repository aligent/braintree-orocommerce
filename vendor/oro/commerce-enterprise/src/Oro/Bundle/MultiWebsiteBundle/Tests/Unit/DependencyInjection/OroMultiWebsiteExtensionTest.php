<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\OroMultiWebsiteExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroMultiWebsiteExtensionTest extends ExtensionTestCase
{
    /**
     * @var array
     */
    protected $extensionConfigs = [];

    public function testLoad()
    {
        $this->loadExtension(new OroMultiWebsiteExtension());
        $expectedDefinitions = [
            'oro_multiwebsite.event_listener.routing',
            'oro_multiwebsite.event_listener.busines_unit_view'
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);

        $this->assertExtensionConfigsLoaded([OroMultiWebsiteExtension::ALIAS]);
    }

    public function testGetAlias()
    {
        $extension = new OroMultiWebsiteExtension();
        $this->assertEquals(OroMultiWebsiteExtension::ALIAS, $extension->getAlias());
    }
}
