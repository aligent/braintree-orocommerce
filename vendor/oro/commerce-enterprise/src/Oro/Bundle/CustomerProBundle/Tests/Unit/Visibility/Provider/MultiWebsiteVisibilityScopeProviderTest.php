<?php

namespace Oro\Bundle\CustomerProBundle\Tests\Unit\Visibility\Provider;

use Oro\Bundle\CustomerProBundle\Visibility\Provider\MultiWebsiteVisibilityScopeProvider;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\CustomerGroupProductVisibility;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\CustomerProductVisibility;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\ProductVisibility;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

class MultiWebsiteVisibilityScopeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScopeManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeManager;

    /**
     * @var Website
     */
    private $website;

    /**
     * @var MultiWebsiteVisibilityScopeProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->scopeManager = $this->getMockBuilder(ScopeManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->website = $this->createMock(Website::class);

        $this->provider = new MultiWebsiteVisibilityScopeProvider($this->scopeManager);
    }

    public function testGetProductVisibilityScope()
    {
        $this->scopeManager
            ->expects($this->once())
            ->method('findOrCreate')
            ->with(ProductVisibility::getScopeType(), [
                ScopeCriteriaProvider::WEBSITE => $this->website
            ]);

        $this->provider->getProductVisibilityScope($this->website);
    }

    public function testGetCustomerProductVisibilityScope()
    {
        $customer = $this->createMock(Customer::class);
        $this->scopeManager
            ->expects($this->once())
            ->method('findOrCreate')
            ->with(CustomerProductVisibility::getScopeType(), [
                ScopeCriteriaProvider::WEBSITE => $this->website,
                ScopeCustomerCriteriaProvider::ACCOUNT => $customer
            ]);

        $this->provider->getCustomerProductVisibilityScope($customer, $this->website);
    }

    public function testGetCustomerGroupProductVisibilityScope()
    {
        $customerGroup = $this->createMock(CustomerGroup::class);
        $this->scopeManager
            ->expects($this->once())
            ->method('findOrCreate')
            ->with(CustomerGroupProductVisibility::getScopeType(), [
                ScopeCriteriaProvider::WEBSITE => $this->website,
                ScopeCustomerGroupCriteriaProvider::FIELD_NAME => $customerGroup
            ]);

        $this->provider->getCustomerGroupProductVisibilityScope($customerGroup, $this->website);
    }
}
