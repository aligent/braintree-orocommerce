<?php

namespace Oro\Bundle\MultiWebsiteBundle\Config;

use Oro\Bundle\ConfigBundle\Config\AbstractScopeManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class WebsiteScopeManager extends AbstractScopeManager
{
    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    /**
     * @var int
     */
    protected $scopeId;

    /**
     * @param WebsiteManager $websiteManager
     */
    public function setWebsiteManager(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopedEntityName()
    {
        return 'website';
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeId()
    {
        $this->ensureScopeIdInitialized();

        return $this->scopeId;
    }

    /**
     * {@inheritdoc}
     */
    public function setScopeId($scopeId)
    {
        $this->scopeId = $scopeId;
    }

    /**
     * {@inheritdoc}
     */
    protected function isSupportedScopeEntity($entity)
    {
        return $entity instanceof Website;
    }

    /**
     * @param Website $entity
     *
     * {@inheritdoc}
     */
    protected function getScopeEntityIdValue($entity)
    {
        return $entity->getId();
    }

    /**
     * Makes sure that the scope id is set
     */
    protected function ensureScopeIdInitialized()
    {
        if (null === $this->scopeId) {
            $scopeId = 0;

            $website = $this->websiteManager->getCurrentWebsite();
            if ($website) {
                $scopeId = $website->getId();
            }

            $this->scopeId = $scopeId;
        }
    }
}
