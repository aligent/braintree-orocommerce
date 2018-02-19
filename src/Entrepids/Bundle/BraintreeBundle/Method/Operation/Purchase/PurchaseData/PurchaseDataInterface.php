<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData;

/**
 * ORO REVIEW:
 * Names of the interface and of the methods are unobvious.
 * It is absolutely unclear what is the purpose of this interface and what implementations this methods should have.
 */
interface PurchaseDataInterface
{
    
    /**
     * @return string
     */
    public function getPurchaseError();
    
    /**
     * @return string
     */
    public function getPurchaseExisting();
    
    /**
     * @return string
     */
    public function getPurchaseNewCreditCard();
    
    /**
     * @return string
     */
    public function getNewCreditCard();
}
