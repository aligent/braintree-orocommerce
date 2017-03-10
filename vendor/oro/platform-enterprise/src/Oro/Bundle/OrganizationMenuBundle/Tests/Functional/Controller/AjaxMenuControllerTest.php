<?php

namespace Oro\Bundle\OrganizationMenuBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\OrganizationMenuBundle\Tests\Functional\DataFixtures\MenuUpdateData;
use Oro\Bundle\OrganizationProBundle\Tests\Functional\Fixture\LoadScopeOrganizationData;

/**
 * @dbIsolation
 */
class AjaxMenuControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures([
            MenuUpdateData::class
        ]);
    }

    public function testCreate()
    {
        $parameters = [
            'menuName' => 'application_menu',
            'parentKey' => 'menu_list_default',
            'scopeId' => $this->getOrganizationScopeId(),
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_navigation_menuupdate_create', $parameters),
            [
                'isDivider' => true
            ]
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_CREATED);
    }

    public function testDelete()
    {
        $parameters = [
            'menuName' => 'application_menu',
            'key' => 'organization_menu_update.1_1',
            'scopeId' => $this->getOrganizationScopeId(),
        ];

        $this->client->request(
            'DELETE',
            $this->getUrl('oro_navigation_menuupdate_delete', $parameters)
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_NO_CONTENT);
    }

    public function testShow()
    {
        $parameters = [
            'menuName' => 'application_menu',
            'key' => 'organization_menu_update.1',
            'scopeId' => $this->getOrganizationScopeId(),
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('oro_navigation_menuupdate_show', $parameters)
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testHide()
    {
        $parameters = [
            'menuName' => 'application_menu',
            'key' => 'organization_menu_update.1',
            'scopeId' => $this->getOrganizationScopeId(),
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('oro_navigation_menuupdate_hide', $parameters)
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testReset()
    {
        $parameters = [
            'menuName' => 'application_menu',
            'scopeId' => $this->getOrganizationScopeId(),
        ];

        $this->client->request(
            'DELETE',
            $this->getUrl('oro_navigation_menuupdate_reset', $parameters)
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_NO_CONTENT);
    }

    public function testMove()
    {
        $parameters = [
            'menuName' => 'application_menu',
            'scopeId' => $this->getOrganizationScopeId(),
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('oro_navigation_menuupdate_move', $parameters),
            [
                'key' => 'organization_menu_update.1',
                'parentKey' => 'application_menu',
                'position' => 33
            ]
        );

        $result = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    /**
     * @return int
     */
    protected function getOrganizationScopeId()
    {
        return $this->getReference(LoadScopeOrganizationData::TEST_ORGANIZATION_SCOPE)->getId();
    }
}
