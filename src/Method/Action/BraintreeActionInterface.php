<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/4/19
 * Time: 1:32 AM
 */

namespace Aligent\BraintreeBundle\Method\Action;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface BraintreeActionInterface
{
    /**
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    public function execute(PaymentTransaction $paymentTransaction);

    /**
     * @param BraintreeConfigInterface $braintreeConfig
     * @return void
     */
    public function initialize(BraintreeConfigInterface $braintreeConfig);

    /**
     * @return string
     */
    public function getName();
}