<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Validate;

use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;

class OperationValidate extends AbstractBraintreeOperation {
	
	const ZERO_AMOUNT = 0;

	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preProcessOperation()
	 */
	protected function preProcessOperation (){
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::postProcessOperation()
	 */
	protected function postProcessOperation (){
		
		$paymentTransaction = $this->paymentTransaction;
		$paymentTransaction->setAmount ( self::ZERO_AMOUNT )->setCurrency ( 'USD' );
		
		
		// sino esta la tarjeta temporalmete poner en false
		$transactionOptions = $paymentTransaction->getTransactionOptions ();
		if (array_key_exists ( 'credit_card_value', $_POST )) {
			$credit_card_value = $_POST ['credit_card_value'];
		} else {
			$paymentTransaction->setSuccessful(false)
			->setActive(false);
			return [];
		}
		
		if (array_key_exists ( 'payment_method_nonce', $_POST )) {
			$nonce = $_POST ["payment_method_nonce"];
		}
		else{
			$nonce = null;
		}
		
		
		
		$transactionOptions ['nonce'] = $nonce;
		$transactionOptions['credit_card_value'] = $credit_card_value;
		$paymentTransaction->setTransactionOptions ( $transactionOptions );
		
		$paymentTransaction->setSuccessful ( true )->setAction ( PaymentMethodInterface::VALIDATE )->setActive ( true );
		
		return [ ];
		
				
	}

	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preprocessDataToSend()
	 */
	protected function preprocessDataToSend (){
		
	}
}