<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Functional\Provider;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

/**
 * @dbIsolation
 */
class WebsiteProviderTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([LoadWebsiteData::class ]);
        $this->client->useHashNavigation(true);
    }

    public function testGetWebsites()
    {
        $websites = $this->getContainer()->get('oro_website.website.provider')->getWebsites();
        $this->assertCount(4, $websites);
    }
}
