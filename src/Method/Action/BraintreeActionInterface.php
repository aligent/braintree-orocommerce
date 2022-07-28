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
     * @return array<string,mixed>
     */
    public function execute(PaymentTransaction $paymentTransaction): array;

    public function initialize(BraintreeConfigInterface $braintreeConfig): void;

    public function getName(): string;
}
