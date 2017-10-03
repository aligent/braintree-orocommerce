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

class ExistingCreditCardPurchase extends AbstractBraintreePurchase {
	

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
	
	protected function postProcessPurchase ( ){
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
		try {
			$customer = $this->adapter->findCustomer ( $this->customerData ['id'] );
			$data = [
					'amount' => $paymentTransaction->getAmount (),
					'customerId' => $this->customerData ['id'], // esto cuando ya existe el cliente y tengo que dar de alta
					// una nueva tarjeta
					'billing' => $this->billingData,
					'shipping' => $this->shipingData,
					'orderId' => $this->identifier,
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
		
			];
		}
		
		$response = $this->adapter->creditCardsale ($token, $data );
		if ($response->success || ! is_null ( $response->transaction )) {
			// Esto es si chage
			$transaction = $response->transaction;
		
			if ($this->isCharge) {
				$paymentTransaction->setAction ( PaymentMethodInterface::PURCHASE )->setActive ( false )->setSuccessful ( $response->success );
			}
		
			// Esto es si authorizr
			if ($this->isAuthorize) {
				$transactionID = $transaction->id;
				$paymentTransaction->setAction ( PaymentMethodInterface::AUTHORIZE )->setActive ( true )->setSuccessful ( $response->success );
		
				$transactionOptions = $paymentTransaction->getTransactionOptions ();
				$transactionOptions ['transactionId'] = $transactionID;
				$paymentTransaction->setTransactionOptions ( $transactionOptions );
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