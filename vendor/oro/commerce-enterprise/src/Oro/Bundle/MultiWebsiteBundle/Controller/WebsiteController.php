<?php

namespace Oro\Bundle\MultiWebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteType;

class WebsiteController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_multiwebsite_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_multiwebsite_view",
     *      type="entity",
     *      class="OroWebsiteBundle:Website",
     *      permission="VIEW"
     * )
     *
     * @param Website $website
     * @return array
     */
    public function viewAction(Website $website)
    {
        return [
            'entity' => $website,
        ];
    }

    /**
     * @Route("/info/{id}", name="oro_multiwebsite_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_multiwebsite_view")
     *
     * @param Website $website
     *
     * @return array
     */
    public function infoAction(Website $website)
    {
        $localizationProvider = $this->get('oro_multiwebsite.provider.website_localization');

        return [
            'website' => $website,
            'localizations' => $localizationProvider->getWebsiteLocalizations($website)
        ];
    }

    /**
     * @Route("/", name="oro_multiwebsite_index")
     * @Template
     * @AclAncestor("oro_multiwebsite_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('oro_website.entity.website.class')
        ];
    }

    /**
     * Create website
     *
     * @Route("/create", name="oro_multiwebsite_create")
     * @Template("OroMultiWebsiteBundle:Website:update.html.twig")
     * @Acl(
     *      id="oro_multiwebsite_create",
     *      type="entity",
     *      class="OroWebsiteBundle:Website",
     *      permission="CREATE"
     * )
     * @return array|RedirectResponse
     */
    public function createAction()
    {
        return $this->update(new Website());
    }

    /**
     * Edit website form
     *
     * @Route("/update/{id}", name="oro_multiwebsite_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_multiwebsite_update",
     *      type="entity",
     *      class="OroWebsiteBundle:Website",
     *      permission="EDIT"
     * )
     *
     * @param Website $website
     * @return array|RedirectResponse
     */
    public function updateAction(Website $website)
    {
        return $this->update($website);
    }

    /**
     * @param Website $website
     *
     * @return array|RedirectResponse
     */
    protected function update(Website $website)
    {
        return $this->get('oro_form.model.update_handler')->handleUpdate(
            $website,
            $this->createForm(WebsiteType::NAME, $website),
            function (Website $website) {
                return [
                    'route' => 'oro_multiwebsite_update',
                    'parameters' => ['id' => $website->getId()]
                ];
            },
            function (Website $website) {
                return [
                    'route' => 'oro_multiwebsite_view',
                    'parameters' => ['id' => $website->getId()]
                ];
            },
            $this->get('translator')->trans('oro.multiwebsite.controller.website.saved.message')
        );
    }
}
