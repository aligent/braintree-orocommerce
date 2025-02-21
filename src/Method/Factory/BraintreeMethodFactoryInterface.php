<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
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
    public function create(BraintreeConfigInterface $config): PaymentMethodInterface;
}
