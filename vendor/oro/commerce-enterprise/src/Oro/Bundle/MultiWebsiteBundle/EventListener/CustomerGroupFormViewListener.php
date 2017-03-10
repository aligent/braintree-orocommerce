<?php

namespace Oro\Bundle\MultiWebsiteBundle\EventListener;

use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\PricingBundle\Entity\PriceListCustomerGroupFallback;
use Oro\Bundle\PricingBundle\Entity\PriceListToCustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;

class CustomerGroupFormViewListener extends AbstractCustomerFormViewListener
{
    /**
     * @var array
     */
    protected $fallbackChoices = [
        PriceListCustomerGroupFallback::CURRENT_ACCOUNT_GROUP_ONLY =>
            'oro.pricing.fallback.current_customer_group_only.label',
        PriceListCustomerGroupFallback::WEBSITE =>
            'oro.pricing.fallback.website.label',
    ];
    
    /**
     * @param BeforeListRenderEvent $event
     */
    public function onCustomerGroupView(BeforeListRenderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            return;
        }
        
        /** @var CustomerGroup $customerGroup */
        $customerGroup = $this->doctrineHelper->getEntityReference(
            'OroCustomerBundle:CustomerGroup',
            (int)$request->get('id')
        );
        
        /** @var PriceListToCustomerGroup[] $priceLists */
        $priceLists = $this->doctrineHelper
            ->getEntityRepository('OroPricingBundle:PriceListToCustomerGroup')
            ->findBy(['customerGroup' => $customerGroup], ['website' => 'ASC']);
        
        /** @var  PriceListCustomerGroupFallback[] $fallbackEntities */
        $fallbackEntities = $this->doctrineHelper
            ->getEntityRepository('OroPricingBundle:PriceListCustomerGroupFallback')
            ->findBy(['customerGroup' => $customerGroup]);
        
        $this->addPriceListInfo(
            $event,
            $priceLists,
            $fallbackEntities,
            $this->websiteProvider->getWebsites(),
            $this->fallbackChoices
        );
    }
}
