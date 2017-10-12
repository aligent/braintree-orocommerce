<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Complete;

use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;

class OperationComplete extends AbstractBraintreeOperation {
	
	
	/**
	 *
	 */
	protected function preProcessOperation (){
		
	}
	
	/**
	 *
	*/
	protected function postProcessOperation (){
		$paymentTransaction = $this->paymentTransaction;
		// Que hay que hacer en esta operacion? Cuando se llama?
		if ($paymentTransaction->getAction () === PaymentMethodInterface::CHARGE) {
			$paymentTransaction->setActive ( false );
		}		
	}
	
	/**
	 *
	*/
	protected function preprocessDataToSend (){
		
	}
	
}