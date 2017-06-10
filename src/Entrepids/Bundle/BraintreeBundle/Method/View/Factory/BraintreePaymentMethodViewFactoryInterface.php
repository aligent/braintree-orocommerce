<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\View\Factory;

use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;

interface BraintreePaymentMethodViewFactoryInterface
{
    /**
     * @param BrainteeConfigInterface $config
     * @return PaymentMethodViewInterface
     */
    public function create(BraintreeConfigInterface $config);
}
