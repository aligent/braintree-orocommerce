<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData;

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
