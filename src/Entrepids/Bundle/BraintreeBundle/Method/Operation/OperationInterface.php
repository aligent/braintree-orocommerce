<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface OperationInterface
{
    /**
     * @param PaymentTransaction $paymentTransaction
     */
    public function operationProcess(PaymentTransaction $paymentTransaction);

    public function setConfig(BraintreeConfig $config);
}
