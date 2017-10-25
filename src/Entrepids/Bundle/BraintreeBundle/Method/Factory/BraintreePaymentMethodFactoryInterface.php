<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Factory;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

interface BraintreePaymentMethodFactoryInterface
{
    /**
     * @param BraintreeConfigInterface $config
     * @return PaymentMethodInterface
     */
    public function create(BraintreeConfigInterface $config);
}
