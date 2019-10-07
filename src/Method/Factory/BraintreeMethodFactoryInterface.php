<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/4/19
 * Time: 1:31 AM
 */

namespace Aligent\BraintreeBundle\Method\Factory;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

interface BraintreeMethodFactoryInterface
{
    /**
     * @param BraintreeConfigInterface $config
     * @return PaymentMethodInterface
     */
    public function create(BraintreeConfigInterface $config);
}