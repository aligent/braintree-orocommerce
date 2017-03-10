<?php

namespace Oro\Bundle\MultiWebsiteBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class GridListener
{
    /**
     * Adds config on website level to the website grid
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $config->offsetSetByPath(
            '[properties][config_link]',
            [
                'type'   => 'url',
                'route'  => 'oro_multiwebsite_config',
                'params' => ['id']
            ]
        );
        $config->offsetSetByPath(
            '[actions][config]',
            [
                'type'         => 'navigate',
                'label'        => 'oro.multiwebsite.grid.action.config',
                'link'         => 'config_link',
                'icon'         => 'cog',
                'acl_resource' => 'oro_multiwebsite_update'
            ]
        );
    }
}
