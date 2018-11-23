<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation;

use Braintree\Transaction;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\ValidationBundle\Validator\Constraints\Integer;

class ChargeOperation extends AbstractBraintreeOperation
{
    // TODO: JOH 21/11/18 I'm not actually sure this class is used at all.  Determine
    // whether it can in fact be removed.

    /**
     *
     * @var Integer
     */
    protected $transactionID;


    /**
     * @inheritDoc
     */
    protected function preProcessOperation()
    {
        $paymentTransaction = $this->paymentTransaction;
        $sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();

        $transactionOptions = $sourcePaymentTransaction->getTransactionOptions();

        if (array_key_exists('transactionId', $transactionOptions)) {
            $this->transactionID = $transactionOptions['transactionId'];
        } else {
            $this->transactionID = null;
        }
    }


    /**
     * @inheritDoc
     */
    protected function postProcessOperation()
    {
        $paymentTransaction = $this->paymentTransaction;
        $sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();

        if ($this->transactionID != null) {
            $response = $this->adapter->submitForSettlement($this->transactionID);

            if (!$response->success) {
                $transactionData = $response->transaction;
                if ($transactionData->status == Transaction::AUTHORIZED) {
                    $paymentTransaction->setSuccessful($response->success)->setActive(true);
                } else {
                    $paymentTransaction->setSuccessful(true)->setActive(false);
                }
            } else {
                $paymentTransaction->setSuccessful($response->success)->setActive(false);
            }

            if ($sourcePaymentTransaction) {
                $paymentTransaction->setActive(false);
            }
            if ($sourcePaymentTransaction &&
                $sourcePaymentTransaction->getAction() !== PaymentMethodInterface::VALIDATE
            ) {
                $sourcePaymentTransaction->setActive(!$paymentTransaction->isSuccessful());
            }

            return [
                'message' => $response->success,
                'successful' => $response->success,
            ];
        } else {
            return [
                'message' => 'No transaction Id',
                'successful' => false,
            ];
        }
    }


    /**
     * @inheritDoc
     */
    protected function preprocessDataToSend()
    {
    }
}
