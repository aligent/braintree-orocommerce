<?php

namespace Entrepids\Bundle\BraintreeBundle\Settings\DataProvider;

interface PaymentActionsDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getPaymentActions();
    /**
     * @return string[]
     */
    public function getCaptureActions();    
}
