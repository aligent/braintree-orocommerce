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

class BraintreeHelper {

	const ZERO_AMOUNT = 0;
	/**
	 * @var BraintreeConfigInterface
	 */
	protected $config;
	
	/**
	 *
	 * @var BraintreeAdapter
	 */
	protected $adapter;
	
	/**
	 * @var DoctrineHelper
	 */
	protected $doctrineHelper;
	
	/**
	 * @var PropertyAccessor
	 */
	protected $propertyAccessor;
	
	/** @var Session */
	protected $session;
	
	protected $translator;
	/**
	 * 
	 * @param BraintreeConfigInterface $config
	 * @param DoctrineHelper $doctrineHelper
	 * @param PropertyAccessor $propertyAccessor
	 * @param Session $session
	 * @param TranslatorInterface $translator
	 */
	public function __construct(BraintreeConfigInterface $config, DoctrineHelper $doctrineHelper, 
			PropertyAccessor $propertyAccessor, Session $session, TranslatorInterface $translator){
		$this->config = $config;
		$this->adapter = new BraintreeAdapter($this->config);
		$this->doctrineHelper = $doctrineHelper;
		$this->propertyAccessor = $propertyAccessor;
		$this->session = $session;
		$this->translator = $translator;		
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function capture(PaymentTransaction $paymentTransaction) {
		$options = $this->getPaymentOptions ( $paymentTransaction );
		$paymentTransaction->setRequest ( $options );
		// Aca tengo que obtener el transactionID y realizar la llamada a Braintree mediante el adapter
		$purchaseAction = $this->config->getPurchaseAction ();
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction ();
	
		// me fijo por las dudas si esta en modo authorize, aunque no se bien...
		$isAuthorize = false;
		if (strcmp ( "authorize", $purchaseAction ) == 0) {
			$isAuthorize = true;
			// hacer lo que tenga que hacer si esta en modo authorize
		}
	
		// aca hacer la llamada a Braintree con el id que supuestamente se genero,
		// sino hay ID entonces preguntar que hacer
	
		if (! $sourcePaymentTransaction) { // esto estaba original de la copia de PAYPAL
			$paymentTransaction->setSuccessful ( false )->setActive ( false );
				
			return [
					'successful' => false
			];
		}
	
		if ($sourcePaymentTransaction->isClone ()) { // esto es original de la copia de PAYPAL
			return $this->charge ( $paymentTransaction );
		}
		else{
			// Y aca que va?
		}
	

	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function charge(PaymentTransaction $paymentTransaction) {
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction ();
	
		$transactionOptions = $sourcePaymentTransaction->getTransactionOptions ();
	
		if (array_key_exists ( 'transactionId', $transactionOptions )) {
			$id = $transactionOptions ['transactionId'];
		} else {
			$id = null;
		}
	
		if ($id != null) { // si existe el id de la transaccion entonces
			return $this->setPaymentCaptureChargeData ( $paymentTransaction, $sourcePaymentTransaction, $id );
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
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function purchase(PaymentTransaction $paymentTransaction) {
		// Aca cambiar por El Adapter o como lo hace Magento
		// $nonce = $this->adapter->createNonce('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		// $this->adapter->find('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction ();
		if ($sourcepaymenttransaction != null) {
			$this->processTransactionPurchase($paymentTransaction);
		} // del $sourcepaymenttransaction != null
		else{
			// esto es cuando $sourcepaymenttransaction es null
			// que se hace en este caso?
		}
	}
	
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function validate(PaymentTransaction $paymentTransaction) {
		$paymentTransaction->setAmount ( self::ZERO_AMOUNT )->setCurrency ( 'USD' );
	
	
		// sino esta la tarjeta temporalmete poner en false
		$transactionOptions = $paymentTransaction->getTransactionOptions ();
		if (array_key_exists ( 'credit_card_value', $_POST )) {
			$credit_card_value = $_POST ['credit_card_value'];
		} else {
			$paymentTransaction->setSuccessful(false)
			->setActive(false);
			return [];
		}
	
		if (array_key_exists ( 'payment_method_nonce', $_POST )) {
			$nonce = $_POST ["payment_method_nonce"];
		}
		else{
			$nonce = null;
		}
	
	
	
		$transactionOptions ['nonce'] = $nonce;
		$transactionOptions['credit_card_value'] = $credit_card_value;
		$paymentTransaction->setTransactionOptions ( $transactionOptions );
	
		$paymentTransaction->setSuccessful ( true )->setAction ( PaymentMethodInterface::VALIDATE )->setActive ( true );
	
		return [ ];
	
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 */
	public function complete(PaymentTransaction $paymentTransaction) {
		if ($paymentTransaction->getAction () === PaymentMethodInterface::CHARGE) {
			$paymentTransaction->setActive ( false );
		}
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 */
	public function authorize(PaymentTransaction $paymentTransaction) {
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction ();
		if ($sourcePaymentTransaction) {
			$this->useValidateTransactionData ( $paymentTransaction, $sourcePaymentTransaction );
				
			return;
		}
	
		$nonce = $_POST ["payment_method_nonce"];
		$transactionOptions = $paymentTransaction->getTransactionOptions ();
		$transactionOptions ['nonce'] = $nonce;
		$paymentTransaction->setTransactionOptions ( $transactionOptions );
		$paymentTransaction->setSuccessful ( true )->setAction ( PaymentMethodInterface::VALIDATE )->setActive ( true );
		// ->setReference($response->getReference())
		// ->setResponse($response->getData());
	}	
	
	/**
	 * {@inheritdoc}
	 */
	public function getIdentifier()
	{
		return $this->config->getPaymentMethodIdentifier();
	}	
	
	/**
	 *
	 * @return PropertyAccessor
	 */
	protected function getPropertyAccessor() {
		return $this->propertyAccessor;
	}
	
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 * @param PaymentTransaction $sourcePaymentTransaction
	 */
	protected function useValidateTransactionData(PaymentTransaction $paymentTransaction, PaymentTransaction $sourcePaymentTransaction) {
		$paymentTransaction->setCurrency ( $sourcePaymentTransaction->getCurrency () )->setReference ( $sourcePaymentTransaction->getReference () )->setSuccessful ( $sourcePaymentTransaction->isSuccessful () )->setActive ( $sourcePaymentTransaction->isActive () )->setRequest ()->setResponse ();
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	protected function getPaymentOptions(PaymentTransaction $paymentTransaction) {
		$options = [
				'AMT' => round ( $paymentTransaction->getAmount (), 2 ),
				'TENDER' => 'C',
				'CURRENCY' => $paymentTransaction->getCurrency ()
		];
	
		if ($paymentTransaction->getSourcePaymentTransaction ()) {
			$options ['ORIGID'] = $paymentTransaction->getSourcePaymentTransaction ()->getReference ();
		}
	
		return $options;
	}	
	
	/**
	 *
	 * @param unknown $paymentTransaction
	 * @param unknown $sourcePaymentTransaction
	 * @param unknown $id
	 */
	private function setPaymentCaptureChargeData(PaymentTransaction $paymentTransaction, PaymentTransaction $sourcePaymentTransaction, $id) {
		$response = $this->adapter->submitForSettlement ( $id );
	
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
	}	
	

	private function processTransactionPurchase (PaymentTransaction $paymentTransaction){
		$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction ();

		$transactionOptions = $sourcepaymenttransaction->getTransactionOptions ();
		$nonce = $transactionOptions ['nonce'];
		if (array_key_exists ( 'credit_card_value', $transactionOptions )) {
			$creditCardValue = $transactionOptions ['credit_card_value'];
		} else {
			$creditCardValue = "newCreditCard";
		}
		
		if ( ( !empty($creditCardValue)) && ( strcmp ( $creditCardValue, "newCreditCard" ) != 0) ) {
			$nonCreditCardValue = new ExistingCreditCardPurchase($this->session, $this->translator,$this->propertyAccessor, $this->doctrineHelper, $this->adapter, $this->config);
			$nonCreditCardValue->setPaymentTransaction($paymentTransaction);
			$nonCreditCardValue->processPurchase();
			
			//$this->nonNewCreditCard($paymentTransaction, $creditCardValue, $customerData, $billingData, $shipingData, $identifier, $isCharge, $isAuthorize);
		} else {
			$newCreditCardValue = new NewCreditCardPurchase($this->session, $this->translator,$this->propertyAccessor, $this->doctrineHelper, $this->adapter, $this->config);
			$newCreditCardValue->setPaymentTransaction($paymentTransaction);
			$newCreditCardValue->processPurchase();			
			
			
			//$this->newCreditCard($paymentTransaction, $customerData, $billingData, $shipingData, $identifier, $nonce, $submitForSettlement, $isCharge, $isAuthorize);
		} // else de nuevaTarjeta de Credito
				
	}
	
}