<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config\Provider;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;

interface BraintreeConfigProviderInterface
{
    /**
     * @return BraintreeConfig[]
     */
    public function getPaymentConfigs();

    /**
     * @param string $identifier
     * @return BraintreeConfig|null
     */
    public function getPaymentConfig($identifier);

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasPaymentConfig($identifier);
}
