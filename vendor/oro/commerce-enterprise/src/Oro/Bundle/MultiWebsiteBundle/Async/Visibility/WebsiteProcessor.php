<?php

namespace Oro\Bundle\MultiWebsiteBundle\Async\Visibility;

use Oro\Bundle\VisibilityBundle\Async\Visibility\AbstractVisibilityProcessor;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class WebsiteProcessor extends AbstractVisibilityProcessor
{
    /**
     * @param object|Website $entity
     */
    protected function resolveVisibilityByEntity($entity)
    {
        $this->cacheBuilder->buildCache($entity);
    }
}
