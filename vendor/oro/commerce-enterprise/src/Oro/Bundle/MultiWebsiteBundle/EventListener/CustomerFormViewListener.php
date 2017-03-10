<?php

namespace Oro\Bundle\MultiWebsiteBundle\EventListener;

use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\PricingBundle\Entity\PriceListCustomerFallback;
use Oro\Bundle\PricingBundle\Entity\PriceListToCustomer;
use Oro\Bundle\CustomerBundle\Entity\Customer;

class CustomerFormViewListener extends AbstractCustomerFormViewListener
{
    /**
     * @var array
     */
    protected $fallbackChoices = [
        PriceListCustomerFallback::CURRENT_ACCOUNT_ONLY =>
            'oro.pricing.fallback.current_customer_only.label',
        PriceListCustomerFallback::ACCOUNT_GROUP =>
            'oro.pricing.fallback.customer_group.label',
    ];

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onCustomerView(BeforeListRenderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }
        
        /** @var Customer $customer */
        $customer = $this->doctrineHelper->getEntityReference('OroCustomerBundle:Customer', (int)$request->get('id'));
        
        /** @var PriceListToCustomer[] $priceLists */
        $priceLists = $this->doctrineHelper
            ->getEntityRepository('OroPricingBundle:PriceListToCustomer')
            ->findBy(['customer' => $customer], ['website' => 'ASC']);
        
        /** @var  PriceListCustomerFallback[] $fallbackEntities */
        $fallbackEntities = $this->doctrineHelper
            ->getEntityRepository('OroPricingBundle:PriceListCustomerFallback')
            ->findBy(['customer' => $customer]);
        
        $this->addPriceListInfo(
            $event,
            $priceLists,
            $fallbackEntities,
            $this->websiteProvider->getWebsites(),
            $this->fallbackChoices
        );
    }
}
