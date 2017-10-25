<?php

namespace Entrepids\Bundle\BraintreeBundle\Helper;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface BraintreeHelperInterface {
	
	/**
	 * 
	 * @param String $paymentOperation
	 */
	public function setPaymentOperation (String $paymentOperation);
	/**
	 * 
	 * @param PaymentTransaction $paymentTransaction
	 * @param String $operation
	 */
	public function execute (PaymentTransaction $paymentTransaction, String $operation);
	
}