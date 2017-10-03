<?php

namespace Entrepids\Bundle\BraintreeBundle\Helper;

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

class NewCreditCardPurchase extends AbstractBraintreePurchase {
	
	protected $nonce;
	
	protected $submitForSettlement;
	
	/**
	 *
	 * @param DoctrineHelper $doctrineHelper
	 * @param BraintreeAdapter $braintreeAdapter
	 */
	public function __construct(Session $session, TranslatorInterface $translator, PropertyAccessor $propertyAccessor, DoctrineHelper $doctrineHelper, BraintreeAdapter $braintreeAdapter, BraintreeConfigInterface $config ){
		$this->doctrineHelper = $doctrineHelper;
		$this->adapter = $braintreeAdapter;
		$this->config = $config;
		$this->propertyAccessor = $propertyAccessor;
		$this->session = $session;
		$this->translator = $translator;
	}
	
	protected function postProcessPurchase (){
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
					'options' => [
							'submitForSettlement' => $this->submitForSettlement,
							'storeInVaultOnSuccess' => $storeInVaultOnSuccess
					]
			];
		}
		
		$response = $this->adapter->sale ( $data );
		
		if ($response->success || ! is_null ( $response->transaction )) {
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
			if ($saveForLater) {
				$creditCardValuesResponse = $transaction->creditCard;
				$token = $creditCardValuesResponse ['token'];
				$this->paymentTransaction->setReference ( $token );
				$this->paymentTransaction->setResponse ( $creditCardValuesResponse );
			}
			$sourcepaymenttransaction->setActive ( false );
		} else {
			$this->processError($response);
		}		
	}
	

	protected function preProcessPurchase (){
		$paymentTransaction = $this->paymentTransaction;
		$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction ();
	
		$transactionOptions = $sourcepaymenttransaction->getTransactionOptions ();
		$nonce = $transactionOptions ['nonce'];
		if (array_key_exists ( 'credit_card_value', $transactionOptions )) {
			$creditCardValue = $transactionOptions ['credit_card_value'];
		} else {
			$creditCardValue = "newCreditCard";
		}
	
		// bueno si el valor de $creditCardValue es newCreditCard es nueva y tengo que hacer todo lo que estaba hasta ahora
		// en cambio sino es asi, eso significa que tiene que existir y es un numero, el cual es el número de transacción
		// que tiene guardado el token
		$this->customerData = $this->getCustomerDataPayment ( $sourcepaymenttransaction );
		$this->shipingData = $this->getOrderAddressPayment ( $sourcepaymenttransaction, 'shippingAddress' );
		$this->billingData = $this->getOrderAddressPayment ( $sourcepaymenttransaction, 'billingAddress' );
	
		$responseTransaction = $paymentTransaction->getResponse ();
		$request = ( array ) $paymentTransaction->getRequest ();
	
		$purchaseAction = $this->config->getPurchaseAction ();
	
		// Para ver si aca ya esta la orden creada
		$entity = $this->doctrineHelper->getEntityReference(
				$paymentTransaction->getEntityClass(),
				$paymentTransaction->getEntityIdentifier()
		);
	
		$orderID = $entity->getId();
		$this->identifier = $entity->getIdentifier();
	
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