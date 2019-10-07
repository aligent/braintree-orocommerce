<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 3:23 AM
 */

namespace Aligent\BraintreeBundle\Method\Config\Provider;


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