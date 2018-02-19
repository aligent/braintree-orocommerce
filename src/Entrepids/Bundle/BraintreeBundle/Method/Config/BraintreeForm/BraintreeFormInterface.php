<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeForm;

/**
 * ORO REVIEW:
 * Interface is never used.
 */
interface BraintreeFormInterface
{
    
    /**
     *
     * @return string
     */
    public function getPaymentMethodNonce();
    
    /**
     *
     * @return string
    */
    public function getBraintreeClientToken();
    
    /**
     *
     * @return string
    */
    public function getCreditCardValue();
    
    /**
     *
     * @return string
    */
    public function getCreditCardFirstValue();
    
    /**
     *
     * @return string
    */
    
    public function getCreditCardsSaved();
}
