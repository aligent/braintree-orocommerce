<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Interfaces;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface OperationInterface
{

    /**
     *
     * @param PaymentTransaction $paymentTransaction
     */
    public function setPaymentTransaction(PaymentTransaction $paymentTransaction);

    /**
     * This method get the payment transaction of the operation
     *
     * @return PaymentTransaction
     */
    public function getPaymentTransaction();

    /**
     * ORO REVIEW:
     * Why a payment transaction couldn't be a argument for this method?
     * In that case you can remove two previous methods, and try to make the implementations immutable.
     *
     * This method is used to process a generic operation
     */
    public function operationProcess();
}
