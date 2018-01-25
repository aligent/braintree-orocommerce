<?php
namespace Entrepids\Bundle\BraintreeBundle\Helper;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface BraintreeHelperInterface
{

    /**
     *
     BraintreeHelper.php* @param PaymentTransaction $paymentTransaction
     * @param String $operation
     */
    public function execute(PaymentTransaction $paymentTransaction, $operation = null);
}
