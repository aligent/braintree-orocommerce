<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Interfaces;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface OperationInterface {
	/**
	 * 
	 * @param PaymentTransaction $paymentTransaction
	 */
	public function setPaymentTransaction(PaymentTransaction $paymentTransaction );
	/**
	 * 
	 */
	public function getPaymentTransaction();
	/**
	 * 
	 */
	public function operationProcess ();
	
}