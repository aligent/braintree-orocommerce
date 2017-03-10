<?php

namespace Oro\Bundle\WebsiteMenuBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Oro\Bundle\WebsiteMenuBundle\DependencyInjection\OroWebsiteMenuExtension;

class OroWebsiteMenuExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new OroWebsiteMenuExtension());

        $expectedServices = [
            'oro_website_menu.scope_criteria_provider.website',
        ];

        $this->assertDefinitionsLoaded($expectedServices);
    }
}
