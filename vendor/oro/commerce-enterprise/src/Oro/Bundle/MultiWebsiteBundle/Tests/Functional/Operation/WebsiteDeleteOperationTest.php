<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Functional\Operation;

use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

/**
 * @dbIsolation
 */
class WebsiteDeleteOperationTest extends ActionTestCase
{
    protected function setUp()
    {
        $this->initClient([], array_merge($this->generateBasicAuthHeader()));
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                'Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData'
            ]
        );
    }

    public function testDelete()
    {
        /** @var Website $website */
        $website = $this->getReference(LoadWebsiteData::WEBSITE1);
        $websiteId = $website->getId();

        $this->assertDeleteOperation($websiteId, 'oro_website.entity.website.class', 'oro_multiwebsite_index');

        $this->client->followRedirects();
        $this->client->request('GET', $this->getUrl('oro_multiwebsite_view', ['id' => $websiteId]));
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }
}
