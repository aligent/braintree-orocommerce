<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Authorize;

use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;

class OperationAuthorize extends AbstractBraintreeOperation {
	
	protected $isValidData;
	/**
	 *
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
	 *
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
	 *
	*/
	protected function preprocessDataToSend (){
		
	}
	
}