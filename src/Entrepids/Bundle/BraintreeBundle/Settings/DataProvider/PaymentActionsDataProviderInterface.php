<?php

namespace Entrepids\Bundle\BraintreeBundle\Settings\DataProvider;

interface PaymentActionsDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getPaymentActions();

    // ORO REVIEW:
    // No usage of this method.
    /**
     * @return string[]
     */
    public function getCaptureActions();    
}
