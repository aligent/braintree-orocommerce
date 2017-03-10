<?php

namespace Oro\Bundle\MultiWebsiteBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\WebsiteSearchBundle\Event\ReindexationRequestEvent;
use Oro\Bundle\WebsiteSearchBundle\EventListener\WebsiteLocalizationConfigListener;

class MultiWebsiteLocalizationConfigListener extends WebsiteLocalizationConfigListener
{
    const SUPPORTED_SCOPE = 'website';

    /**
     * {@inheritdoc}
     */
    protected function getReindexationRequestEvent(ConfigUpdateEvent $event)
    {
        $websitesIds = $event->getScope() === static::SUPPORTED_SCOPE && $event->getScopeId() !== null
            ? [$event->getScopeId()]
            : [];

        return new ReindexationRequestEvent([], $websitesIds);
    }
}
