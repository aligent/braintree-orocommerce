<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase;

use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;

class PurchaseErrorOperation  extends AbstractBraintreeOperation {
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preProcessOperation()
	 */
	protected function preProcessOperation (){
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase::postProcessOperation()
	 */
	protected function postProcessOperation (){
		$paymentTransaction = $this->paymentTransaction;
		
		$paymentTransaction->setAction ( $this->paymentOperation )->setActive ( false )->setSuccessful ( false );
		$paymentTransaction->getSourcePaymentTransaction()->setActive ( false )->setSuccessful ( false );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase::preprocessDataToSend()
	 */
	protected function preprocessDataToSend (){
		
	}
	
}