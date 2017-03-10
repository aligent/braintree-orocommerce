<?php

namespace Oro\Bundle\OrganizationMenuBundle\Bundle\Tests\Functional\Controller;

use Oro\Bundle\NavigationBundle\Entity\MenuUpdate;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\OrganizationMenuBundle\Tests\Functional\DataFixtures\MenuUpdateData;
use Oro\Bundle\OrganizationProBundle\Tests\Functional\Fixture\LoadScopeOrganizationData;

/**
 * @dbIsolation
 */
class OrganizationMenuControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures([
            MenuUpdateData::class,
            LoadScopeOrganizationData::class
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_organization_menu_index', ['id' => 1]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testView()
    {
        $url = $this->getUrl(
            'oro_organization_menu_view',
            ['scopeId' => $this->getOrganizationScopeId(), 'menuName' => 'application_menu']
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains(
            'Select existing menu item or create new.',
            $crawler->filter('.content .text-center')->html()
        );
    }

    public function testCreate()
    {
        $url = $this->getUrl(
            'oro_organization_menu_create',
            ['scopeId' => $this->getOrganizationScopeId(), 'menuName' => 'application_menu']
        );
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form['oro_navigation_menu_update[titles][values][default]'] = 'menu_update.new.title.default';
        $form['oro_navigation_menu_update[descriptions][values][default]'] = 'menu_update.new.description.default';
        $form['oro_navigation_menu_update[uri]'] = '#menu_update.new';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('Menu item saved successfully.', $crawler->html());
    }

    public function testCreateChild()
    {
        $url = $this->getUrl('oro_organization_menu_create', [
            'scopeId' => $this->getOrganizationScopeId(),
            'menuName' => 'application_menu',
            'parentKey' => 'organization_menu_update.1'
        ]);
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form['oro_navigation_menu_update[titles][values][default]'] = 'menu_update.child.title.default';
        $form['oro_navigation_menu_update[descriptions][values][default]'] = 'menu_update.child.description.default';
        $form['oro_navigation_menu_update[uri]'] = '#menu_update.child';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('Menu item saved successfully.', $crawler->html());
    }

    public function testUpdateCustom()
    {
        /** @var MenuUpdate $reference */
        $reference = $this->getReference('organization_menu_update.1_1');

        $url = $this->getUrl('oro_organization_menu_update', [
            'scopeId' => $this->getOrganizationScopeId(),
            'menuName' => 'application_menu',
            'key' => $reference->getKey()
        ]);
        $crawler = $this->client->request('GET', $url);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $form = $crawler->selectButton('Save')->form();
        $form['oro_navigation_menu_update[titles][values][default]'] = 'menu_update.changed.title.default';
        $form['oro_navigation_menu_update[descriptions][values][default]'] = 'menu_update.changed.description.default';
        $form['oro_navigation_menu_update[uri]'] = '#menu_update.changed';

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $html = $crawler->html();
        $this->assertContains('Menu item saved successfully.', $html);
        $this->assertContains('menu_update.changed.title.default', $html);
    }

    /**
     * @return int
     */
    protected function getOrganizationScopeId()
    {
        return $this->getReference(LoadScopeOrganizationData::TEST_ORGANIZATION_SCOPE)->getId();
    }
}
