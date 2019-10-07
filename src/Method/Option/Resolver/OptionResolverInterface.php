<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/11/19
 * Time: 2:41 AM
 */

namespace Aligent\BraintreeBundle\Method\Option\Resolver;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface OptionResolverInterface
{
    /**
     * @param PaymentTransaction $paymentTransaction
     * @param BraintreeConfigInterface $config
     * @return array
     */
    public function resolveOptions(PaymentTransaction $paymentTransaction, BraintreeConfigInterface $config);
}