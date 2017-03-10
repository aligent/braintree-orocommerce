<?php

namespace Oro\Bundle\CustomerProBundle\Visibility\Provider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\CustomerGroupProductVisibility;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\CustomerProductVisibility;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\ProductVisibility;
use Oro\Bundle\VisibilityBundle\Provider\VisibilityScopeProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

class MultiWebsiteVisibilityScopeProvider extends VisibilityScopeProvider
{
    /**
     * @param Website $website
     * @return \Oro\Bundle\ScopeBundle\Entity\Scope
     */
    public function getProductVisibilityScope(Website $website)
    {
        return $this->scopeManager->findOrCreate(
            ProductVisibility::getScopeType(),
            [
                ScopeCriteriaProvider::WEBSITE => $website,
            ]
        );
    }

    /**
     * @param Customer $customer
     * @param Website $website
     * @return \Oro\Bundle\ScopeBundle\Entity\Scope
     */
    public function getCustomerProductVisibilityScope(Customer $customer, Website $website)
    {
        return $this->scopeManager->findOrCreate(
            CustomerProductVisibility::getScopeType(),
            [
                ScopeCriteriaProvider::WEBSITE => $website,
                ScopeCustomerCriteriaProvider::ACCOUNT => $customer
            ]
        );
    }

    /**
     * @param CustomerGroup $customerGroup
     * @param Website $website
     * @return \Oro\Bundle\ScopeBundle\Entity\Scope
     */
    public function getCustomerGroupProductVisibilityScope(CustomerGroup $customerGroup, Website $website)
    {
        return $this->scopeManager->findOrCreate(
            CustomerGroupProductVisibility::getScopeType(),
            [
                ScopeCriteriaProvider::WEBSITE => $website,
                ScopeCustomerGroupCriteriaProvider::FIELD_NAME => $customerGroup
            ]
        );
    }
}
