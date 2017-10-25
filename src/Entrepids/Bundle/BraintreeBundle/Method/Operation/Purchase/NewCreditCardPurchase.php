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

class NewCreditCardPurchase extends AbstractBraintreePurchase {

	/**
	 * 
	 * @var String
	 */
	protected $nonce;
	
	/**
	 * 
	 * @var Boolean
	 */
	protected $submitForSettlement;
	
	/**
	 * 
	 * @var Boolean
	 */
	protected $saveForLater;
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Helper\AbstractBraintreePurchase::getResponseFromBraintree()
	 */
	protected function getResponseFromBraintree(){
		$sourcepaymenttransaction = $this->getPaymentTransaction()->getSourcePaymentTransaction ();
		$transactionOptions = $sourcepaymenttransaction->getTransactionOptions ();
		$saveForLater = false;
		
		if (array_key_exists ( 'saveForLaterUse', $transactionOptions )) {
			$saveForLater = $transactionOptions ['saveForLaterUse'];
		}		
		$storeInVaultOnSuccess = false;
		if ($saveForLater) {
			$storeInVaultOnSuccess = true; // aca esta el caso que tengo que guardar los datos de la tarjeta
		} else {
			$storeInVaultOnSuccess = false; // o el usuario no selecciono el checkbox o por configuracion no esta habilitado
		}
		
		// Esto es para ver si el cliente exite en Braintree y sino es asi entonces le mando los datos
		$merchAccountID = $this->config->getSandBoxMerchAccountId();
		try {
			$customer = $this->adapter->findCustomer ( $this->customerData ['id'] );
			$data = [
					'amount' => $this->paymentTransaction->getAmount (),
					'paymentMethodNonce' => $this->nonce,
					'customerId' => $this->customerData ['id'], // esto cuando ya existe el cliente y tengo que dar de alta
					// una nueva tarjeta
					'billing' => $this->billingData,
					'shipping' => $this->shipingData,
					'orderId' => $this->identifier,
					'merchantAccountId' => $merchAccountID,
					'options' => [
							'submitForSettlement' => $this->submitForSettlement,
							'storeInVaultOnSuccess' => $storeInVaultOnSuccess
					]
			];
		} catch ( NotFound $e ) {
			$data = [
					'amount' => $this->paymentTransaction->getAmount (),
					'paymentMethodNonce' => $this->nonce,
					'customer' => $this->customerData, // esto si es nuevo lo tengo que enviar
					// 'customerId' => 'the_customer_id', // esto cuando ya existe el cliente y tengo que dar de alta
					// una nueva tarjeta
					'billing' => $this->billingData,
					'shipping' => $this->shipingData,
					'orderId' => $this->identifier,
					'merchantAccountId' => $merchAccountID,
					'options' => [
							'submitForSettlement' => $this->submitForSettlement,
							'storeInVaultOnSuccess' => $storeInVaultOnSuccess
					]
			];
		}
		
		$response = $this->adapter->sale ( $data );
		
		return $response;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase::setDataToPreProcessResponse()
	 */
	protected function setDataToPreProcessResponse (){
		$sourcepaymenttransaction = $this->getPaymentTransaction()->getSourcePaymentTransaction ();
		$transactionOptions = $sourcepaymenttransaction->getTransactionOptions ();
		$saveForLater = false;
		if (array_key_exists ( 'saveForLaterUse', $transactionOptions )) {
			$saveForLater = $transactionOptions ['saveForLaterUse'];
		}
		
		$this->saveForLater = $saveForLater;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase::processSuccess()
	 */
	protected function processSuccess ($response){
	
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
	
	
		// $paymentTransaction->setReference($reference);
		// Para la parte del token id de la tarjeta de credito
		if ($this->saveForLater) {
			$creditCardValuesResponse = $transaction->creditCard;
			$token = $creditCardValuesResponse ['token'];
			$this->paymentTransaction->setReference ( $token );
			$this->paymentTransaction->setResponse ( $creditCardValuesResponse );
		}
		$this->paymentTransaction->getSourcePaymentTransaction()->setActive ( false );
	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Helper\AbstractBraintreePurchase::preProcessPurchase()
	 */
	protected function preProcessOperation(){
		$paymentTransaction = $this->paymentTransaction;
		$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction ();
	
		$transactionOptions = $sourcepaymenttransaction->getTransactionOptions ();
		$nonce = $transactionOptions ['nonce'];

		$purchaseAction = $this->config->getPurchaseAction ();
		// authorize or charge
		// si charge mando true
		// si authorize mando false
		$submitForSettlement = true;
		$isAuthorize = false;
		$isCharge = false;
		if (strcmp ( "authorize", $purchaseAction ) == 0) {
			$submitForSettlement = false;
			$isAuthorize = true;
		}
		if (strcmp ( "charge", $purchaseAction ) == 0) {
			$submitForSettlement = true;
			$isCharge = true;
		}
		
		$this->submitForSettlement = $submitForSettlement;
		$this->nonce = $nonce;
		$this->isAuthorize = $isAuthorize;
		$this->isCharge = $isCharge;
	}
}