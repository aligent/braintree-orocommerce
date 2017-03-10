<?php

namespace Oro\Bundle\OrganizationProBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\OrganizationProBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class OverrideServiceCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessSkip()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();
        $containerMock->expects($this->exactly(15))
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_email.mailbox.manager'),
                    $this->equalTo('oro_email.listener.datagrid.mailbox_grid'),
                    $this->equalTo('oro_email.provider.email_recipients.helper'),
                    $this->equalTo('oro_entity_extend.datagrid.extension.dynamic_fields'),
                    $this->equalTo('oro_entity_extend.twig.extension.dynamic_fields'),
                    $this->equalTo('oro_entity_extend.form.extension.dynamic_fields'),
                    $this->equalTo('oro_organization.form.extension.organization'),
                    $this->equalTo('oro_organization.form.extension.owner'),
                    $this->equalTo('oro_report.listener.navigation_listener'),
                    $this->equalTo('oro_organization.form.type.business_unit'),
                    $this->equalTo('oro_windows.twig.extension'),
                    $this->equalTo('oro_organization.validator.owner'),
                    $this->equalTo('oro_organization.listener.choice_tree_filter_load_data_listener'),
                    $this->equalTo('oro_organization.form.type.organizations_select'),
                    $this->equalTo('oro_organization.autocomplete.business_unit_tree_search_handler')
                )
            )
            ->will($this->returnValue(false));

        $containerMock
            ->expects($this->any())
            ->method('getDefinition')
            ->with('oro_security.security_facade');

        $compilerPass = new OverrideServiceCompilerPass();
        $compilerPass->process($containerMock);
    }

    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->setMethods([])
            ->getMock();
        $definition
            ->expects($this->exactly(11))
            ->method('setClass')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\Entity\Manager\MailboxManager'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\EventListener\MailboxGridListener'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\Provider\EmailRecipientsHelper'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\Grid\DynamicFieldsExtension'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\Twig\DynamicFieldsExtension'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\Form\Extension\DynamicFieldsExtension'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\Form\Extension\OwnerProFormExtension'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\Form\Type\BusinessUnitProType'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\Twig\WindowsExtension'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\Validator\Constraints\OwnerValidator'),
                    $this->equalTo('Oro\Bundle\OrganizationProBundle\EventListener\ChoiceTreeFilterLoadDataListener')
                )
            )
            ->will($this->returnSelf());
        $definition
            ->expects($this->exactly(4))
            ->method('addArgument')
            ->will($this->returnSelf());
        $definition
            ->expects($this->any())
            ->method('addMethodCall')
            ->will($this->returnSelf());

        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $containerMock->expects($this->exactly(15))
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_email.mailbox.manager'),
                    $this->equalTo('oro_email.listener.datagrid.mailbox_grid'),
                    $this->equalTo('oro_email.provider.email_recipients.helper'),
                    $this->equalTo('oro_entity_extend.datagrid.extension.dynamic_fields'),
                    $this->equalTo('oro_entity_extend.twig.extension.dynamic_fields'),
                    $this->equalTo('oro_entity_extend.form.extension.dynamic_fields'),
                    $this->equalTo('oro_organization.form.extension.organization'),
                    $this->equalTo('oro_organization.form.extension.owner'),
                    $this->equalTo('oro_report.listener.navigation_listener'),
                    $this->equalTo('oro_organization.form.type.business_unit'),
                    $this->equalTo('oro_windows.twig.extension'),
                    $this->equalTo('oro_organization.validator.owner'),
                    $this->equalTo('oro_organization.listener.choice_tree_filter_load_data_listener'),
                    $this->equalTo('oro_organization.form.type.organizations_select'),
                    $this->equalTo('oro_organization.autocomplete.business_unit_tree_search_handler')
                )
            )
            ->will($this->returnValue(true));

        $containerMock->expects($this->exactly(19))
            ->method('getDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_email.mailbox.manager'),
                    $this->equalTo('oro_email.listener.datagrid.mailbox_grid'),
                    $this->equalTo('oro_email.provider.email_recipients.helper'),
                    $this->equalTo('oro_entity_extend.datagrid.extension.dynamic_fields'),
                    $this->equalTo('oro_entity_extend.twig.extension.dynamic_fields'),
                    $this->equalTo('oro_entity_extend.form.extension.dynamic_fields'),
                    $this->equalTo('oro_security.security_facade'),
                    $this->equalTo('oro_organization.form.extension.organization'),
                    $this->equalTo('oro_organization.form.extension.owner'),
                    $this->equalTo('oro_report.listener.navigation_listener'),
                    $this->equalTo('oro_organization.form.type.business_unit'),
                    $this->equalTo('oro_windows.twig.extension'),
                    $this->equalTo('security.context'),
                    $this->equalTo('oro_organization.validator.owner'),
                    $this->equalTo('oro_organization.listener.choice_tree_filter_load_data_listener'),
                    $this->equalTo('oro_organization.form.type.organizations_select'),
                    $this->equalTo('oro_organization.autocomplete.business_unit_tree_search_handler')
                )
            )

            ->will($this->returnValue($definition));

        $compilerPass = new OverrideServiceCompilerPass();
        $compilerPass->process($containerMock);
    }
}
