<?php

namespace Oro\Bundle\OrganizationMenuBundle\Controller;

use Oro\Bundle\NavigationBundle\Controller\AbstractMenuController;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ScopeBundle\Entity\Scope;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/menu/organization")
 */
class OrganizationMenuController extends AbstractMenuController
{
    /**
     * @Route("/{id}", name="oro_organization_menu_index")
     * @Template
     *
     * @param Organization $organization
     * @return array
     */
    public function indexAction(Organization $organization)
    {
        $response = parent::index();
        $response['organization'] = $organization;

        return $response;
    }

    /**
     * @Route("/{scopeId}/{menuName}", name="oro_organization_menu_view")
     * @ParamConverter("scope", class="OroScopeBundle:Scope", options={"id" = "scopeId"})
     * @Template
     *
     * @param string $menuName
     * @param Scope  $scope
     * @return array
     */
    public function viewAction(Scope $scope, $menuName)
    {
        return parent::view($menuName, $this->getContext($scope), $this->getMenuTreeContext($scope));
    }

    /**
     * @Route("/{scopeId}/{menuName}/create/{parentKey}", name="oro_organization_menu_create")
     * @ParamConverter("scope", class="OroScopeBundle:Scope", options={"id" = "scopeId"})
     * @Template("OroOrganizationMenuBundle:OrganizationMenu:update.html.twig")
     *
     * @param Scope       $scope
     * @param string      $menuName
     * @param string|null $parentKey
     * @return array|RedirectResponse
     */
    public function createAction(Scope $scope, $menuName, $parentKey = null)
    {
        return parent::create($menuName, $parentKey, $this->getContext($scope), $this->getMenuTreeContext($scope));
    }

    /**
     * @Route("/{scopeId}/{menuName}/update/{key}", name="oro_organization_menu_update")
     * @ParamConverter("scope", class="OroScopeBundle:Scope", options={"id" = "scopeId"})
     * @Template
     *
     * @param Scope  $scope
     * @param string $menuName
     * @param string $key
     * @return array|RedirectResponse
     */
    public function updateAction(Scope $scope, $menuName, $key)
    {
        return parent::update($menuName, $key, $this->getContext($scope), $this->getMenuTreeContext($scope));
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeType()
    {
        return $this->getParameter('oro_navigation.menu_update.scope_type');
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenuUpdateManager()
    {
        return $this->get('oro_navigation.manager.menu_update');
    }

    /**
     * @param Scope $scope
     * @return array
     */
    protected function getContext(Scope $scope)
    {
        return ['organization' => $scope->getOrganization()];
    }

    /**
     * @param Scope $scope
     * @return array
     */
    private function getMenuTreeContext(Scope $scope)
    {
        return ['organization' => $scope->getOrganization()];
    }

    /**
     * {@inheritDoc}
     */
    protected function checkAcl()
    {
        if (!$this->get('oro_security.security_facade')->isGranted('oro_organization_update')) {
            throw $this->createAccessDeniedException();
        }
    }
}
