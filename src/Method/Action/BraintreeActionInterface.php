<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
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