<?php

namespace Oro\Bundle\OrganizationProBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function process(ContainerBuilder $container)
    {
        /**
         * This override is responsible for making all mailboxes available to users logged under global organization.
         */
        $mailboxManagerId = 'oro_email.mailbox.manager';
        if ($container->hasDefinition($mailboxManagerId)) {
            $mailboxManagerDef = $container->getDefinition($mailboxManagerId);
            $mailboxManagerDef->setClass('Oro\Bundle\OrganizationProBundle\Entity\Manager\MailboxManager');
        }

        /**
         * Shows mailboxes for all organizations in system configuration if logged under global organization
         */
        $mailboxGridListenerId = 'oro_email.listener.datagrid.mailbox_grid';
        if ($container->hasDefinition($mailboxGridListenerId)) {
            $mailboxListenerDef = $container->getDefinition($mailboxGridListenerId);
            $mailboxListenerDef->setClass('Oro\Bundle\OrganizationProBundle\EventListener\MailboxGridListener');
            $mailboxListenerDef->addMethodCall('setSecurityFacade', [new Reference('oro_security.security_facade')]);
        }

        /**
         * Extension is responsible for showing organization in global organizaton in recipients autocomplete
         */
        $emailRecipientsHelperId = 'oro_email.provider.email_recipients.helper';
        if ($container->hasDefinition($emailRecipientsHelperId)) {
            $emailRecipientsHelperDef = $container->getDefinition($emailRecipientsHelperId);
            $emailRecipientsHelperDef->setClass('Oro\Bundle\OrganizationProBundle\Provider\EmailRecipientsHelper');
            $emailRecipientsHelperDef->addMethodCall(
                'setSecurityFacade',
                [new Reference('oro_security.security_facade')]
            );
        }

        /**
         * Override Oro\Bundle\EntityExtendBundle\Grid\DynamicFieldsExtension
         * Extension is responsible for columns of custom fields on grids
         */
        $extendFieldsExtensionId = 'oro_entity_extend.datagrid.extension.dynamic_fields';
        if ($container->hasDefinition($extendFieldsExtensionId)) {
            $dynamicFieldsExtensionDef = $container->getDefinition($extendFieldsExtensionId);
            $dynamicFieldsExtensionDef->setClass('Oro\Bundle\OrganizationProBundle\Grid\DynamicFieldsExtension');
            $dynamicFieldsExtensionDef->addArgument($container->getDefinition('oro_security.security_facade'));
        }

        /**
         * Override Oro\Bundle\EntityExtendBundle\Twig\DynamicFieldsExtension
         * Extension is responsible for custom fields on view pages
         */
        $twigFieldExtensionId = 'oro_entity_extend.twig.extension.dynamic_fields';
        if ($container->hasDefinition($twigFieldExtensionId)) {
            $twigFieldExtensionDef = $container->getDefinition($twigFieldExtensionId);
            $twigFieldExtensionDef->setClass('Oro\Bundle\OrganizationProBundle\Twig\DynamicFieldsExtension');
            $twigFieldExtensionDef->addArgument($container->getDefinition('oro_security.security_facade'));
        }

        /**
         * Override Oro\Bundle\EntityExtendBundle\Form\Extension\DynamicFieldsExtension
         * Extension is responsible for custom fields on edit pages
         */
        $formFieldExtensionId = 'oro_entity_extend.form.extension.dynamic_fields';
        if ($container->hasDefinition($formFieldExtensionId)) {
            $formFieldExtensionDef = $container->getDefinition($formFieldExtensionId);
            $formFieldExtensionDef->setClass('Oro\Bundle\OrganizationProBundle\Form\Extension\DynamicFieldsExtension');
            $formFieldExtensionDef->addArgument($container->getDefinition('oro_security.security_facade'));
            $formFieldExtensionDef->addArgument(new Reference('oro_organizationpro.system_mode_org_provider'));
        }

        /**
         * Override Oro\Bundle\OrganizationBundle\Form\Extension\OrganizationFormExtension
         * Add security facade, system access mode organization provider and doctrine helper
         */
        $organizationExtensionId = 'oro_organization.form.extension.organization';
        if ($container->hasDefinition($organizationExtensionId)) {
            $organizationExtensionDef = $container->getDefinition($organizationExtensionId);
            $organizationExtensionDef->addMethodCall(
                'setSecurityFacade',
                [$container->getDefinition('oro_security.security_facade')]
            );
            $organizationExtensionDef->addMethodCall(
                'setOrganizationProvider',
                [new Reference('oro_organizationpro.system_mode_org_provider')]
            );
            $organizationExtensionDef->addMethodCall(
                'setDoctrineHelper',
                [new Reference('oro_entity.doctrine_helper')]
            );
        }

        /**
         * Override Oro\Bundle\OrganizationBundle\Form\Extension\OwnerFormExtension
         * Add system access mode organization provider
         */
        $ownerExtensionId = 'oro_organization.form.extension.owner';
        if ($container->hasDefinition($ownerExtensionId)) {
            $ownerExtensionDef = $container->getDefinition($ownerExtensionId);
            $ownerExtensionDef->setClass('Oro\Bundle\OrganizationProBundle\Form\Extension\OwnerProFormExtension');
            $ownerExtensionDef->addMethodCall(
                'setOrganizationProvider',
                [new Reference('oro_organizationpro.system_mode_org_provider')]
            );
        }

        /**
         * Override Oro\Bundle\ReportBundle\EventListener\NavigationListener
         * Add system access mode organization provider
         */
        $navigationListenerId = 'oro_report.listener.navigation_listener';
        if ($container->hasDefinition($navigationListenerId)) {
            $navigationListenerDef = $container->getDefinition($navigationListenerId);
            $navigationListenerDef->addMethodCall(
                'setOrganizationProvider',
                [new Reference('oro_organizationpro.system_mode_org_provider')]
            );
        }

        /**
         * Override Oro\Bundle\OrganizationBundle\Form\Type\BusinessUnitType.
         * Add system access mode organization provider
         */
        $businessUnitTypeId = 'oro_organization.form.type.business_unit';
        if ($container->hasDefinition($businessUnitTypeId)) {
            $businessUnitTypeDef = $container->getDefinition($businessUnitTypeId);
            $businessUnitTypeDef->setClass('Oro\Bundle\OrganizationProBundle\Form\Type\BusinessUnitProType');
            $businessUnitTypeDef->addMethodCall(
                'setOrganizationProvider',
                [new Reference('oro_organizationpro.system_mode_org_provider')]
            );
        }

        /**
         * Override Oro\Bundle\EntityExtendBundle\Twig\DynamicFieldsExtension
         * Dialog two step form render
         */
        $windowsExtensionId = 'oro_windows.twig.extension';
        if ($container->hasDefinition($windowsExtensionId)) {
            $windowsExtensionDef = $container->getDefinition($windowsExtensionId);
            $windowsExtensionDef->setClass('Oro\Bundle\OrganizationProBundle\Twig\WindowsExtension');
            $windowsExtensionDef->addMethodCall(
                'setRouter',
                [new Reference('router')]
            );
        }

        /**
         * Override Oro\Bundle\OrganizationBundle\Validator\Constraints\OwnerValidator
         * In case of System access mode, we should take organization from the entity
         */
        $ownerValidatorId = 'oro_organization.validator.owner';
        if ($container->hasDefinition($ownerValidatorId)) {
            $ownerValidatorDef = $container->getDefinition($ownerValidatorId);
            $ownerValidatorDef->setClass('Oro\Bundle\OrganizationProBundle\Validator\Constraints\OwnerValidator');
        }

        /**
         * Shows organization in filters of grid if logged under global organization
         */
        $choiceTreeFilterListenerId = 'oro_organization.listener.choice_tree_filter_load_data_listener';
        if ($container->hasDefinition($choiceTreeFilterListenerId)) {
            $choiceTreeFilterListenerDef = $container->getDefinition($choiceTreeFilterListenerId);
            $choiceTreeFilterListenerDef->setClass(
                'Oro\Bundle\OrganizationProBundle\EventListener\ChoiceTreeFilterLoadDataListener'
            );
            $choiceTreeFilterListenerDef->addMethodCall(
                'setSecurityFacade',
                [new Reference('oro_security.security_facade')]
            );
        }

        $this->overrideOrganizationsSelect($container);

        $businessUnitTreeSearchHandlerId = 'oro_organization.autocomplete.business_unit_tree_search_handler';
        if ($container->hasDefinition($businessUnitTreeSearchHandlerId)) {
            $businessUnitTreeSearchHandlerDef = $container->getDefinition($businessUnitTreeSearchHandlerId);
            $businessUnitTreeSearchHandlerDef->addMethodCall(
                'setOrganizationProHelper',
                [new Reference('oro_organizationpro.helper')]
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function overrideOrganizationsSelect(ContainerBuilder $container)
    {
        $organizationsSelectId = 'oro_organization.form.type.organizations_select';
        if (!$container->hasDefinition($organizationsSelectId)) {
            return;
        }

        $organizationsSelectDef = $container->getDefinition($organizationsSelectId);
        $organizationsSelectDef->addMethodCall(
            'setOrganizationProHelper',
            [new Reference('oro_organizationpro.helper')]
        );
    }
}
