<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Charge;

use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use Oro\Bundle\ValidationBundle\Validator\Constraints\Integer;

class OperationCharge extends AbstractBraintreeOperation
{

    /**
     *
     * @var Integer
     */
    protected $transactionID;

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preProcessOperation()
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
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::postProcessOperation()
     */
    protected function postProcessOperation()
    {
        $paymentTransaction = $this->paymentTransaction;
        $sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
        
        if ($this->transactionID != null) {
            $response = $this->adapter->submitForSettlement($this->transactionID);
            
            if (! $response->success) {
                $errors = $response->message;
                $transactionData = $response->transaction;
                $status = $transactionData->__get('status');
                
                if (strcmp($status, Braintree\Transaction::AUTHORIZED) == 0) {
                    $paymentTransaction->setSuccessful($response->success)->setActive(true);
                } else {
                    $paymentTransaction->setSuccessful(true)->setActive(false);
                }
            } else {
                $errors = 'No errors';
                $paymentTransaction->setSuccessful($response->success)->setActive(false);
            }
            
            if ($sourcePaymentTransaction) {
                $paymentTransaction->setActive(false);
            }
            if ($sourcePaymentTransaction
                &&
                $sourcePaymentTransaction->getAction() !== PaymentMethodInterface::VALIDATE
                ) {
                $sourcePaymentTransaction->setActive(! $paymentTransaction->isSuccessful());
            }
            
            return [
                'message' => $response->success,
                'successful' => $response->success
            ];
        } else {
            return [
                'message' => 'No transaction Id',
                'successful' => false
            ];
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preprocessDataToSend()
     */
    protected function preprocessDataToSend()
    {
    }
}
