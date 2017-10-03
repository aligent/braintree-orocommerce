<?php

namespace Entrepids\Bundle\BraintreeBundle\Helper;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface PurchaseInterface {
	
	public function setPaymentTransaction(PaymentTransaction $paymentTransaction );
	
	public function getPaymentTransaction();
	
	public function processPurchase ();
	
}