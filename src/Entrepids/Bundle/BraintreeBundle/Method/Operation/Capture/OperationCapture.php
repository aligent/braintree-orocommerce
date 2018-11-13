<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Capture;

use BeSimple\SoapCommon\Type\KeyValue\Boolean;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\ValidationBundle\Validator\Constraints\Integer;

/**
 * This class capture the data of the payment
 */
class OperationCapture extends AbstractBraintreeOperation
{

    /**
     *
     * @var Integer
     */
    protected $transactionId;

    /**
     *
     * @var Boolean
     */
    protected $isAuthorize;

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preProcessOperation()
     */
    protected function preProcessOperation()
    {
        $paymentTransaction = $this->paymentTransaction;
        $options = [
            'AMT' => round($paymentTransaction->getAmount(), 2),
            'TENDER' => 'C',
            'CURRENCY' => $paymentTransaction->getCurrency()
        ];
        
        if ($paymentTransaction->getSourcePaymentTransaction()) {
            $options['ORIGID'] = $paymentTransaction->getSourcePaymentTransaction()->getReference();
        }
        
        $paymentTransaction->setRequest($options);
        
        $purchaseAction = $this->config->getPurchaseAction();
        
        $this->isAuthorize = false;
        if (strcmp("authorize", $purchaseAction) == 0) {
            $this->isAuthorize = true;
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
        if (! $sourcePaymentTransaction) {
            $paymentTransaction->setSuccessful(false)->setActive(false);
            
            return [
                'successful' => false
            ];
        } else {
            if ($this->transactionId != null) {
                $response = $this->adapter->submitForSettlement($this->transactionId);
                
                if (! $response->success) {
                    $errors = $response->message;
                    $transactionData = $response->transaction;
                    // ORO REVIEW:
                    // Implicit magic method calls should be avoided as these methods are used by PHP internals.
                    $status = $transactionData->__get('status');
                    $paymentTransaction->setSuccessful(true)->setActive(false);
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
                
                $this->paymentTransaction->setReference($this->transactionId);
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
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preprocessDataToSend()
     */
    protected function preprocessDataToSend()
    {
        $paymentTransaction = $this->paymentTransaction;
        $sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
        if (! $sourcePaymentTransaction) {
            $paymentTransaction->setSuccessful(false)->setActive(false);
        } else {
            $sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
            
            $transactionOptions = $sourcePaymentTransaction->getTransactionOptions();
            
            if (array_key_exists('transactionId', $transactionOptions)) {
                $this->transactionId = $transactionOptions['transactionId'];
            } else {
                $this->transactionId = null;
            }
        }
    }
}
