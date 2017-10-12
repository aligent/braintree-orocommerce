<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Capture;

use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;

class OperationCapture extends AbstractBraintreeOperation {
	
	protected $transactionId;
	
	protected $isAuthorize;

	/**
	 *
	 */
	protected function preProcessOperation (){
		$paymentTransaction = $this->paymentTransaction;
		//$options = $this->getPaymentOptions ( $paymentTransaction );
		$options = [
				'AMT' => round ( $paymentTransaction->getAmount (), 2 ),
				'TENDER' => 'C',
				'CURRENCY' => $paymentTransaction->getCurrency ()
		];
		
		if ($paymentTransaction->getSourcePaymentTransaction ()) {
			$options ['ORIGID'] = $paymentTransaction->getSourcePaymentTransaction ()->getReference ();
		}
		
		$paymentTransaction->setRequest ( $options );
		// Aca tengo que obtener el transactionID y realizar la llamada a Braintree mediante el adapter
		$purchaseAction = $this->config->getPurchaseAction ();
	
		// me fijo por las dudas si esta en modo authorize, aunque no se bien...
		$this->isAuthorize = false;
		if (strcmp ( "authorize", $purchaseAction ) == 0) {
			$this->isAuthorize = true;
			// hacer lo que tenga que hacer si esta en modo authorize
		}
	
	}
	
	/**
	 *
	*/
	protected function postProcessOperation (){
		$paymentTransaction = $this->paymentTransaction;
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction ();
		if (! $sourcePaymentTransaction) { // esto estaba original de la copia de PAYPAL
			$paymentTransaction->setSuccessful ( false )->setActive ( false );
	
			return [
			 'successful' => false
			];
		}
		else{
			if ($this->transactionId != null) { // si existe el id de la transaccion entonces
				$response = $this->adapter->submitForSettlement ( $this->transactionId );
				
				if (! $response->success) {
					$errors = $response->message;
					$transactionData = $response->transaction;
					$status = $transactionData->__get ( 'status' );
				
					if (strcmp ( $status, Braintree\Transaction::AUTHORIZED ) == 0) { // esto es lo que dice la clase Transaction del modulo Braintree
						// es estado authorizado y fallo
						$paymentTransaction->setSuccessful ( $response->success )->setActive ( true );
						// ->setReference($response->getReference()) // no estoy seguro, lo saco hasta que sepa que va
						// ->setResponse($response->getData()); // ni idea que puede ser data, lo saco hasta que sepa
					} else {
						// es otro estado y fallo, aca tengo que poner la transaccion que ya fue capturada previamente
						$paymentTransaction->setSuccessful ( true )-> // lo pongo en true porque no es estado authorized
						setActive ( false );
						// ->setReference($response->getReference()) // no estoy seguro, lo saco hasta que sepa que va
						// ->setResponse($response->getData()); // ni idea que puede ser data, lo saco hasta que sepa
					}
				} else {
					$errors = 'No errors';
					$paymentTransaction->setSuccessful ( $response->success )->setActive ( false );
					// ->setReference($response->getReference()) // no estoy seguro, lo saco hasta que sepa que va
					// ->setResponse($response->getData()); // ni idea que puede ser data, lo saco hasta que sepa
				}
				
				if ($sourcePaymentTransaction) {
					$paymentTransaction->setActive ( false );
				}
				if ($sourcePaymentTransaction && $sourcePaymentTransaction->getAction () !== PaymentMethodInterface::VALIDATE) {
					$sourcePaymentTransaction->setActive ( ! $paymentTransaction->isSuccessful () );
				}
				
				
				return [
						'message' => $response->success,
						'successful' => $response->success
				];
				
			} else { // no existe el id de la transaccion
				// dejo la transaccion y la orden como estaba??

				return [
				 'message' => 'No transaction Id',
				 'successful' => false
				];
			}		
		}
	}
	/**
	 *
	*/
	protected function preprocessDataToSend (){
		$paymentTransaction = $this->paymentTransaction;
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction ();
		if (! $sourcePaymentTransaction) { // esto estaba original de la copia de PAYPAL
			$paymentTransaction->setSuccessful ( false )->setActive ( false );

		}
		else {
			$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction ();
			
			$transactionOptions = $sourcePaymentTransaction->getTransactionOptions ();
			
			if (array_key_exists ( 'transactionId', $transactionOptions )) {
				$this->transactionId = $transactionOptions ['transactionId'];
			} else {
				$this->transactionId = null;
			}			
		}

	}
}