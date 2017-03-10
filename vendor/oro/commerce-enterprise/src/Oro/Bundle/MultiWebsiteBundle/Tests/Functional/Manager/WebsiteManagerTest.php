<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Functional\Manager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MultiWebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

/**
 * @dbIsolation
 */
class WebsiteManagerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], array_merge($this->generateBasicAuthHeader()));
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadWebsiteData::class
            ]
        );
    }

    public function testSetDefault()
    {
        /** @var Website $website */
        $website = $this->getReference(LoadWebsiteData::WEBSITE2);

        /** @var WebsiteManager $manager */
        $manager = $this->getContainer()->get('oro_website.manager');
        $manager->setDefaultWebsite($website);

        $actual = $this->getContainer()->get('doctrine')->getRepository(Website::class)->findBy(['default' => true]);
        $this->assertCount(1, $actual);
        $this->assertSame($website->getId(), reset($actual)->getId());
    }
}
