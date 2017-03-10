<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\MultiWebsiteBundle\EventListener\GridListener;

class GridListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnBuildBefore()
    {
        $gridConfig = DatagridConfiguration::create(
            [
                'properties' => [],
                'actions' => []
            ]
        );

        /** @var DatagridInterface|\PHPUnit_Framework_MockObject_MockObject $dataGrid */
        $dataGrid = $this->createMock(DatagridInterface::class);

        $event = new BuildBefore($dataGrid, $gridConfig);
        $listener = new GridListener();
        $listener->onBuildBefore($event);

        $this->assertEquals(
            [
                'type'   => 'url',
                'route'  => 'oro_multiwebsite_config',
                'params' => ['id']
            ],
            $gridConfig->offsetGetByPath('[properties][config_link]')
        );
        $this->assertEquals(
            [
                'type'         => 'navigate',
                'label'        => 'oro.multiwebsite.grid.action.config',
                'link'         => 'config_link',
                'icon'         => 'cog',
                'acl_resource' => 'oro_multiwebsite_update'
            ],
            $gridConfig->offsetGetByPath('[actions][config]')
        );
    }
}
