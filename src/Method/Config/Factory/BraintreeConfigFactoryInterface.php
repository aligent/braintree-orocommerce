<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 3:09 AM
 */

namespace Aligent\BraintreeBundle\Method\Config\Factory;


use Aligent\BraintreeBundle\Entity\BraintreeIntegrationSettings;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;

interface BraintreeConfigFactoryInterface
{
    /**
     * @param BraintreeIntegrationSettings $settings
     * @return BraintreeConfigInterface
     */
    public function create(BraintreeIntegrationSettings $settings);
}