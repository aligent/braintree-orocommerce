<?php

namespace Oro\Bundle\OrganizationConfigBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

use Oro\Bundle\OrganizationConfigBundle\EventListener\OrganizationGridListener;

class OrganizationGridListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnBuildBefore()
    {
        $gridConfig = DatagridConfiguration::create(
            [
                'properties' => [],
                'actions' => []
            ]
        );
        $event = new BuildBefore(
            $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Datagrid')
                ->disableOriginalConstructor()
                ->getMock(),
            $gridConfig
        );
        $listener = new OrganizationGridListener();
        $listener->onBuildBefore($event);

        $this->assertEquals(
            [
                'type'   => 'url',
                'route'  => 'oro_organization_config',
                'params' => ['id']
            ],
            $gridConfig->offsetGetByPath('[properties][config_link]')
        );
        $this->assertEquals(
            [
                'type'         => 'navigate',
                'label'        => 'oro.organization_config.grid.action.config',
                'link'         => 'config_link',
                'icon'         => 'cog',
                'acl_resource' => 'oro_organization_update'
            ],
            $gridConfig->offsetGetByPath('[actions][config]')
        );
    }
}
