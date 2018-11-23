<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\View\Factory;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;

interface BraintreePaymentMethodViewFactoryInterface
{
    /**
     * @param BrainteeConfigInterface $config
     * @return PaymentMethodViewInterface
     */
    public function create(BraintreeConfig $config);
}
