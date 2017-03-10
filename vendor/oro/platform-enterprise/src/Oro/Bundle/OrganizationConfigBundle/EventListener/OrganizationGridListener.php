<?php

namespace Oro\Bundle\OrganizationConfigBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class OrganizationGridListener
{
    /**
     * Adds config on organization level to the organization grid
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
                'route'  => 'oro_organization_config',
                'params' => ['id']
            ]
        );
        $config->offsetSetByPath(
            '[actions][config]',
            [
                'type'         => 'navigate',
                'label'        => 'oro.organization_config.grid.action.config',
                'link'         => 'config_link',
                'icon'         => 'cog',
                'acl_resource' => 'oro_organization_update'
            ]
        );
    }
}
