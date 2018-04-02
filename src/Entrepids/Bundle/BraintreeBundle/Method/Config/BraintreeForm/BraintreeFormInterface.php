<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeForm;

// eliminarla y chequear que funcione todo
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
