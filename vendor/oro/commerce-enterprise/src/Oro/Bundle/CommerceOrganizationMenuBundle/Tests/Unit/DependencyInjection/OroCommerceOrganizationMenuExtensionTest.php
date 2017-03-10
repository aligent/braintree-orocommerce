<?php

namespace Oro\Bundle\CommerceOrganizationMenuBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CommerceOrganizationMenuBundle\DependencyInjection\OroCommerceOrganizationMenuExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroCommerceOrganizationMenuExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new OroCommerceOrganizationMenuExtension());

        $expectedServices = [
            'oro_commerce_organization_menu.scope_criteria_provider.organization',
        ];

        $this->assertDefinitionsLoaded($expectedServices);
    }
}
