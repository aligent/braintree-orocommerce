<?php

namespace Oro\Bundle\MultiWebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class ConfigurationController extends Controller
{
    /**
     * @Route(
     *      "/website/{id}/{activeGroup}/{activeSubGroup}",
     *      name="oro_multiwebsite_config",
     *      requirements={"id"="\d+"},
     *      defaults={"activeGroup" = null, "activeSubGroup" = null}
     * )
     * @Template()
     * @AclAncestor("oro_organization_update")
     *
     * @param Request $request
     * @param Website $entity
     * @param string|null $activeGroup
     * @param string|null $activeSubGroup
     * @return array
     */
    public function websiteConfigAction(
        Request $request,
        Website $entity,
        $activeGroup = null,
        $activeSubGroup = null
    ) {
        $provider = $this->get('oro_multiwebsite.provider.form_provider');
        /** @var ConfigManager $manager */
        $manager = $this->get('oro_config.website');
        $prevScopeId = $manager->getScopeId();
        $manager->setScopeId($entity->getId());

        list($activeGroup, $activeSubGroup) = $provider->chooseActiveGroups($activeGroup, $activeSubGroup);

        $tree = $provider->getTree();
        $form = false;

        if ($activeSubGroup !== null) {
            $form = $provider->getForm($activeSubGroup);

            if ($this->get('oro_config.form.handler.config')
                ->setConfigManager($manager)
                ->process($form, $request)
            ) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('oro.config.controller.config.saved.message')
                );

                // outdate content tags, it's only special case for generation that are not covered by NavigationBundle
                $taggableData = ['name' => 'website_configuration', 'params' => [$activeGroup, $activeSubGroup]];
                $sender       = $this->get('oro_sync.content.topic_sender');

                $sender->send($sender->getGenerator()->generate($taggableData));

                // recreate form to drop values for fields with use_parent_scope_value
                $form = $provider->getForm($activeSubGroup);
                $form->setData($manager->getSettingsByForm($form));
            }
        }
        $manager->setScopeId($prevScopeId);

        return array(
            'entity'         => $entity,
            'data'           => $tree,
            'form'           => $form ? $form->createView() : null,
            'activeGroup'    => $activeGroup,
            'activeSubGroup' => $activeSubGroup,
        );
    }
}
