<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config\Provider;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;

interface BraintreeConfigProviderInterface
{
    /**
     * @return BraintreeConfigInterface[]
     */
    public function getPaymentConfigs();

    /**
     * @param string $identifier
     * @return BraintreeConfigInterface|null
     */
    public function getPaymentConfig($identifier);

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasPaymentConfig($identifier);
}
