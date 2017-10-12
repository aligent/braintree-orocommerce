<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Charge;

use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;

class OperationCharge extends AbstractBraintreeOperation {
	
	protected $transactionID;
	
	/**
	 *
	 */
	protected function preProcessOperation (){
		$paymentTransaction = $this->paymentTransaction;
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction ();
		
		$transactionOptions = $sourcePaymentTransaction->getTransactionOptions ();
		
		if (array_key_exists ( 'transactionId', $transactionOptions )) {
			$this->transactionID = $transactionOptions ['transactionId'];
		} else {
			$this->transactionID = null;
		}
		
	}
	
	/**
	 *
	*/
	protected function postProcessOperation (){
		$paymentTransaction = $this->paymentTransaction;
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction ();
		
		if ($this->transactionID != null) { // si existe el id de la transaccion entonces
			//return $this->setPaymentCaptureChargeData ( $paymentTransaction, $sourcePaymentTransaction, $id );
			$response = $this->adapter->submitForSettlement ( $this->transactionID );
			
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
	/**
	 *
	*/
	protected function preprocessDataToSend (){
		
	}
	
	
}