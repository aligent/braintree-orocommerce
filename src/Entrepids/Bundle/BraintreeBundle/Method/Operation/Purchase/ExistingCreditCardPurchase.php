<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase;

use Braintree\Exception\NotFound;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase;

class ExistingCreditCardPurchase extends AbstractBraintreePurchase {
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Helper\AbstractBraintreePurchase::getResponseFromBraintree()
	 */
	protected function getResponseFromBraintree(){
		$paymentTransaction = $this->getPaymentTransaction();
		$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction ();
		
		$transactionOptions = $sourcepaymenttransaction->getTransactionOptions ();
		if (array_key_exists ( 'credit_card_value', $transactionOptions )) {
			$creditCardValue = $transactionOptions ['credit_card_value'];
		} else {
			$creditCardValue = "newCreditCard";
		}
		
		$paymentTransactionEntity = $this->doctrineHelper->getEntityRepository(PaymentTransaction::class)->findOneBy([
				'id' => $creditCardValue,
		]);
		
		
		$token = $paymentTransactionEntity->getReference();
		$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction ();
		// Esto es para ver si el cliente exite en Braintree y sino es asi entonces le mando los datos
		$merchAccountID = $this->config->getSandBoxMerchAccountId();
		try {
			$customer = $this->adapter->findCustomer ( $this->customerData ['id'] );
			$data = [
					'amount' => $paymentTransaction->getAmount (),
					'customerId' => $this->customerData ['id'], // esto cuando ya existe el cliente y tengo que dar de alta
					// una nueva tarjeta
					'billing' => $this->billingData,
					'shipping' => $this->shipingData,
					'orderId' => $this->identifier,
					'merchantAccountId' => $merchAccountID,
			];
		} catch ( NotFound $e ) {
			$data = [
					'amount' => $paymentTransaction->getAmount (),
					'customer' => $this->customerData, // esto si es nuevo lo tengo que enviar
					// 'customerId' => 'the_customer_id', // esto cuando ya existe el cliente y tengo que dar de alta
					// una nueva tarjeta
					'billing' => $this->billingData,
					'shipping' => $this->shipingData,
					'orderId' => $this->identifier,
					'merchantAccountId' => $merchAccountID,
		
			];
		}
		
		$response = $this->adapter->creditCardsale ($token, $data );
		return $response;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase::setDataToPreProcessResponse()
	 */
	protected function setDataToPreProcessResponse (){
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase::processSuccess()
	 */
	protected function processSuccess ( $response){
		
		// Esto es si chage
		$transaction = $response->transaction;
	
		if ($this->isCharge) {
			$this->paymentTransaction->setAction ( PaymentMethodInterface::PURCHASE )->setActive ( false )->setSuccessful ( $response->success );
		}
	
		// Esto es si authorizr
		if ($this->isAuthorize) {
			$transactionID = $transaction->id;
			$this->paymentTransaction->setAction ( PaymentMethodInterface::AUTHORIZE )->setActive ( true )->setSuccessful ( $response->success );
	
			$transactionOptions = $this->paymentTransaction->getTransactionOptions ();
			$transactionOptions ['transactionId'] = $transactionID;
			$this->paymentTransaction->setTransactionOptions ( $transactionOptions );
		}
	
	
		$this->paymentTransaction->getSourcePaymentTransaction()->setActive ( false );

			
	}	

	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Helper\AbstractBraintreePurchase::preProcessPurchase()
	 */
	protected function preProcessOperation(){

		$purchaseAction = $this->config->getPurchaseAction ();
		// authorize or charge
		// si charge mando true
		// si authorize mando false
		$isAuthorize = false;
		$isCharge = false;
		if (strcmp ( "authorize", $purchaseAction ) == 0) {
			$isAuthorize = true;
		}
		if (strcmp ( "charge", $purchaseAction ) == 0) {
			$isCharge = true;
		}
		
		$this->isAuthorize = $isAuthorize;
		$this->isCharge = $isCharge;
	}
}