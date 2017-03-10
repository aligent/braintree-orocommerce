<?php

namespace Oro\Bundle\OrganizationMenuBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;

class OrganizationMenuGridListener
{
    /** @var string */
    private $scopeType;

    /** @var ScopeManager  */
    private $scopeManager;

    const PATH_VIEW_LINK_ID = '[properties][view_link][direct_params][scopeId]';

    /**
     * @param ScopeManager $scopeManager
     */
    public function __construct(ScopeManager $scopeManager)
    {
        $this->scopeManager = $scopeManager;
    }

    /**
     * @param $scopeType
     */
    public function setScopeType($scopeType)
    {
        $this->scopeType = $scopeType;
    }

    /**
     * Adds config on organization level to the organization grid
     *
     * @param PreBuild $event
     */
    public function onPreBefore(PreBuild $event)
    {
        $scope = $this->scopeManager->findOrCreate(
            $this->scopeType,
            ['organization' => $event->getParameters()->get('organization')]
        );

        $config = $event->getConfig();
        $config->offsetSetByPath(self::PATH_VIEW_LINK_ID, $scope->getId());
    }
}
