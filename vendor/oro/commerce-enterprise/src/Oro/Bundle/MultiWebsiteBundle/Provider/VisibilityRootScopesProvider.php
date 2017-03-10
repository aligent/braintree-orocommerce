<?php

namespace Oro\Bundle\MultiWebsiteBundle\Provider;

use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\ProductVisibility;
use Oro\Bundle\VisibilityBundle\Provider\VisibilityRootScopesProviderInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class VisibilityRootScopesProvider implements VisibilityRootScopesProviderInterface
{
    /**
     * @var ScopeManager
     */
    protected $scopeManager;

    /**
     * @param ScopeManager $scopeManager
     */
    public function __construct(ScopeManager $scopeManager)
    {
        $this->scopeManager = $scopeManager;
    }

    /**
     * @return Scope[]
     */
    public function getScopes()
    {
        $scopes = $this->scopeManager->findRelatedScopes(ProductVisibility::getScopeType());
        if (0 === count($scopes)) {
            $scopes = [$this->scopeManager->findDefaultScope()];
        }
        $result = [];
        foreach ($scopes as $scope) {
            /** @var Website $website */
            $website = $scope->getWebsite();
            $label = 'Default';

            if (null !== $website) {
                $label = $website->getName();
            }
            $result[$label] = $scope;
        }

        return $result;
    }
}
