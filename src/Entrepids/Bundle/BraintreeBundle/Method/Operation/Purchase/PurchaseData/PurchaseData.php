<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData;

class PurchaseData implements PurchaseDataInterface
{
    
    /**
     * @internal
     */
    const PURCHASE_ERROR = 'purchaseError';
    
    /**
     * @internal
     */
    const PURCHASE_EXISTING = 'purchaseExisting';
    
    /**
     * @internal
     */
    const PURCHASE_NEWCREDITCARD = 'purchaseNewCreditCard';
    
    /**
     * @internal
     */
    const NEWCREDITCARD = 'newCreditCard';

    /**
     * (non-PHPdoc)
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseDataInterface::getPurchaseError()
     */
    public function getPurchaseError()
    {
        return self::PURCHASE_ERROR;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseDataInterface::getPurchaseExisting()
     */
    public function getPurchaseExisting()
    {
        return self::PURCHASE_EXISTING;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseDataInterface::getPurchaseNewCreditCard()
     */
    public function getPurchaseNewCreditCard()
    {
        return self::PURCHASE_NEWCREDITCARD;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseDataInterface::getNewCreditCard()
     */
    public function getNewCreditCard()
    {
        return self::NEWCREDITCARD;
    }
}
