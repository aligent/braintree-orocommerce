<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Oro\Bundle\WebsiteElasticSearchBundle\DependencyInjection\OroWebsiteElasticSearchExtension;

class OroWebsiteElasticSearchExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new OroWebsiteElasticSearchExtension());

        $expectedParameters = [];
        $this->assertParametersLoaded($expectedParameters);

        $expectedDefinitions = [];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }

    public function testGetAlias()
    {
        $extension = new OroWebsiteElasticSearchExtension();
        $this->assertSame('oro_website_elastic_search', $extension->getAlias());
    }
}
