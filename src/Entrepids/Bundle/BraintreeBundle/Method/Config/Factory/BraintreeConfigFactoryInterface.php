<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config\Factory;

use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;

interface BraintreeConfigFactoryInterface
{

    /**
     *
     * @param BraintreeSettings $settings
     * @return BraintreeConfig
     */
    public function create(BraintreeSettings $settings);
}
