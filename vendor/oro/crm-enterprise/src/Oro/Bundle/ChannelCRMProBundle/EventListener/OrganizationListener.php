<?php

namespace Oro\Bundle\ChannelCRMProBundle\EventListener;

use Oro\Bundle\OrganizationProBundle\Event\OrganizationUpdateEvent;

use Oro\Bundle\ChannelBundle\Provider\StateProvider;

class OrganizationListener
{
    /** @var StateProvider */
    protected $stateProvider;

    /**
     * @param StateProvider $stateProvider
     */
    public function __construct(StateProvider $stateProvider)
    {
        $this->stateProvider = $stateProvider;
    }

    /**
     * @param OrganizationUpdateEvent $event
     */
    public function onUpdateOrganization(OrganizationUpdateEvent $event)
    {
        $organization = $event->getOrganization();

        $this->stateProvider->clearOrganizationCache($organization->getId());
    }
}
