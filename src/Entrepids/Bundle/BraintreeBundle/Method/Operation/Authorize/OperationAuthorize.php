<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Authorize;

use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use BeSimple\SoapCommon\Type\KeyValue\Boolean;

class OperationAuthorize extends AbstractBraintreeOperation {
	
	/**
	 * 
	 * @var Boolean
	 */
	protected $isValidData;
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preProcessOperation()
	 */
	protected function preProcessOperation (){
		$paymentTransaction = $this->paymentTransaction;
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction ();
		if ($sourcePaymentTransaction) {
			$paymentTransaction->setCurrency ( $sourcePaymentTransaction->getCurrency () )->setReference ( $sourcePaymentTransaction->getReference () )->setSuccessful ( $sourcePaymentTransaction->isSuccessful () )->setActive ( $sourcePaymentTransaction->isActive () )->setRequest ()->setResponse ();
			$this->isValidData = false;;
		}
		else{
			$this->isValidData = true;
		}
				
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::postProcessOperation()
	 */
	protected function postProcessOperation (){
		$paymentTransaction = $this->paymentTransaction;
		
		if ($this->isValidData){
			$nonce = $_POST ["payment_method_nonce"];
			$transactionOptions = $paymentTransaction->getTransactionOptions ();
			$transactionOptions ['nonce'] = $nonce;
			$paymentTransaction->setTransactionOptions ( $transactionOptions );
			$paymentTransaction->setSuccessful ( true )->setAction ( PaymentMethodInterface::VALIDATE )->setActive ( true );
			// ->setReference($response->getReference())
			// ->setResponse($response->getData());
					
		}
	
				
	}

	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preprocessDataToSend()
	 */
	protected function preprocessDataToSend (){
		
	}
	
}