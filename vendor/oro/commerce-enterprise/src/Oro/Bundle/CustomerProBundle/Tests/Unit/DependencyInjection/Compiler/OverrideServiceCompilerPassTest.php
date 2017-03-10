<?php

namespace Oro\Bundle\CustomerProBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\CustomerProBundle\Datagrid\RolePermissionDatasource;
use Oro\Bundle\CustomerProBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class OverrideServiceCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $containerBuilder;

    protected function setUp()
    {
        $this->containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testProcessSkip()
    {
        $this->containerBuilder->expects($this->exactly(2))
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_customer.datagrid.datasource.customer_role_frontend_permission_datasource'),
                    $this->equalTo('oro_customer.datagrid.datasource.customer_role_permission_datasource')
                )
            )
            ->willReturn(false);

        $this->containerBuilder->expects($this->never())->method('getDefinition');

        $compilerPass = new OverrideServiceCompilerPass();
        $compilerPass->process($this->containerBuilder);
    }

    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->setMethods([])
            ->getMock();

        $definition->expects($this->exactly(2))
            ->method('setClass')
            ->with(RolePermissionDatasource::class)
            ->willReturnSelf();
        $definition->expects($this->exactly(2))
            ->method('addMethodCall')
            ->with('addExcludePermission', ['SHARE'])
            ->willReturnSelf();

        $this->containerBuilder->expects($this->exactly(2))
            ->method('hasDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_customer.datagrid.datasource.customer_role_frontend_permission_datasource'),
                    $this->equalTo('oro_customer.datagrid.datasource.customer_role_permission_datasource')
                )
            )
            ->willReturn(true);

        $this->containerBuilder->expects($this->exactly(2))
            ->method('getDefinition')
            ->with(
                $this->logicalOr(
                    $this->equalTo('oro_customer.datagrid.datasource.customer_role_frontend_permission_datasource'),
                    $this->equalTo('oro_customer.datagrid.datasource.customer_role_permission_datasource')
                )
            )
            ->willReturn($definition);

        $compilerPass = new OverrideServiceCompilerPass();
        $compilerPass->process($this->containerBuilder);
    }
}
