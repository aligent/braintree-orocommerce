<?php

namespace Oro\Bundle\OrganizationProBundle\Form\Type;

use Doctrine\ORM\PersistentCollection;

use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Form\Type\OrganizationsSelectType;

use Oro\Bundle\OrganizationProBundle\Helper\OrganizationProHelper;

class OrganizationsProSelectType extends OrganizationsSelectType
{
    /** @var OrganizationProHelper */
    protected $organizationProHelper;

    /** @var array */
    protected $businessUnitsTree;

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults([
            'show_organizations_selector' => !$this->organizationProHelper->isGlobalOrganizationExists() ||
                $this->securityFacade->getOrganization()->getIsGlobal(),
            'label' => function (Options $options) {
                return $options['show_organizations_selector']
                    ? 'oro.user.form.access_settings.label'
                    : 'oro.user.form.business_units.label';
            }
        ]);
    }

    /**
     * @param OrganizationProHelper $organizationProHelper
     */
    public function setOrganizationProHelper(OrganizationProHelper $organizationProHelper)
    {
        $this->organizationProHelper = $organizationProHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['show_organizations_selector'] = $options['show_organizations_selector'];

        $buTree = $this->getFormBusinessUnitsTree();
        $view->vars['organization_tree_ids'] = $buTree;

        /** @var PersistentCollection $organizationsData */
        $organizationsData = $view->vars['data']->getOrganizations();
        if ($organizationsData) {
            $organizationsData = $organizationsData->map(
                function ($item) {
                    return $item->getId();
                }
            )->getValues();
        }

        /** @var PersistentCollection $businessUnitData */
        $businessUnitData = $view->vars['data']->getBusinessUnits();
        if ($businessUnitData) {
            $businessUnitData = $businessUnitData->map(
                function ($item) {
                    return $item->getId();
                }
            )->getValues();
        }

        $view->vars['selected_organizations']  = $organizationsData;
        $view->vars['selected_business_units'] = $businessUnitData;
        $view->vars['accordion_enabled'] = $this->buManager->getTreeNodesCount($buTree) > 1000;
    }

    /**
     * {@inheritdoc}
     */
    protected function addOrganizationsField(FormBuilderInterface $builder)
    {
        $builder->add(
            'organizations',
            'entity',
            [
                'class'    => 'OroOrganizationBundle:Organization',
                'property' => 'name',
                'multiple' => true,
                // we cannot use AclProtectedQueryBuilderLoader that is uses to all platform's entity type fields
                // by default cause in case of edit role page we should have access to all the organizations records
                // without ACL checks.
                'loader'   => new ORMQueryBuilderLoader(
                    $this->em->getRepository(Organization::class)->createQueryBuilder('e')
                ),
            ]
        );
    }

    /**
     * @return array
     */
    protected function getFormBusinessUnitsTree()
    {
        return array_intersect_key(
            $this->getBusinessUnitsTree(),
            array_flip($this->getOrganizationOptionsIds())
        );
    }

    /**
     * @return array
     */
    protected function getBusinessUnitsTree()
    {
        if ($this->businessUnitsTree === null) {
            $this->businessUnitsTree = $this->buManager->getBusinessUnitRepo()->getOrganizationBusinessUnitsTree(
                null,
                ['is_global' => 'DESC']
            );
        }

        return $this->businessUnitsTree;
    }

    /**
     * @return int[]
     */
    protected function getOrganizationOptionsIds()
    {
        $ids = [];
        $organizations = $this->getAvailableOrganizations();
        foreach ($organizations as $organization) {
            $ids[] = $organization->getId();
        }

        return $ids;
    }

    /**
     * Return organizations can be edited by current user
     *
     * @return Organization[]
     */
    protected function getAvailableOrganizations()
    {
        if ($this->securityFacade->getOrganization()->getIsGlobal()) {
            return $this->em->getRepository(Organization::class)->findAll();
        }

        if ($this->organizationProHelper->isGlobalOrganizationExists()) {
            return [$this->securityFacade->getOrganization()];
        }

        return $this->getLoggedInUser()->getOrganizations(false);
    }
}
