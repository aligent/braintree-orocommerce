<?php

namespace Entrepids\Bundle\BraintreeBundle\Method;

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


class Braintree implements PaymentMethodInterface {
	const TYPE = 'braintree';
	const COMPLETE = 'complete';
	const ZERO_AMOUNT = 0;
	
	/**
	 * @var BraintreeConfigInterface
	 */
	private $config;
	
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
	
	/**
	 * @var SurchargeProvider
	 */
	protected $surchargeProvider;
	
	/** @var Session */
	protected $session;
	
	protected $translator;
	
	/**
	 *
	 * @param BraintreeConfigInterface $config        	    	
	 * @param BraintreeAdapter $adapter        	
	 * @param DoctrineHelper $doctrineHelper        	   	
	 * @param PropertyAccessor $propertyAccessor        	
	 */
	public function __construct(BraintreeConfigInterface $config, BraintreeAdapter $adapter, DoctrineHelper $doctrineHelper, 
			PropertyAccessor $propertyAccessor, Session $session, TranslatorInterface $translator) {
		$this->config = $config;
		$this->adapter = $adapter;
		$this->doctrineHelper = $doctrineHelper;
		$this->propertyAccessor = $propertyAccessor;
		$this->session = $session;
		$this->translator = $translator;
	}
	

	/**
	 * {@inheritdoc}
	 */
	public function execute($action, PaymentTransaction $paymentTransaction) {
		if (! $this->supports ( $action )) {
			throw new \InvalidArgumentException ( sprintf ( 'Unsupported action "%s"', $action ) );
		}
		
		return $this->{$action} ( $paymentTransaction ) ?  : [ ];
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getType() {
		return self::TYPE;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function isEnabled() {
		return $this->config->isEnabled ();
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function isApplicable(PaymentContextInterface $context) {
		/*
		 * return $this->config->isCountryApplicable($context)
		 * && $this->config->isCurrencyApplicable($context);
		 */
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function supports($actionName) {
		if ($actionName === self::VALIDATE) {
			return true;
		}
		
		return in_array ( ( string ) $actionName, [ 
				self::AUTHORIZE,
				self::CAPTURE,
				self::CHARGE,
				self::PURCHASE,
				self::COMPLETE 
		], true );
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
		
		// aca va si no es un clone???
		// revisar esta parte que hacer cuando continua por aca y en que casos se puede dar
		$response = $this->gateway->request ( Option\Transaction::DELAYED_CAPTURE, $this->combineOptions ( $options ) ); // esto es original y tengo que ver
		                                                                                 // que va en la respuesta o es lo que devuelve el metodo charge
		
		unset ( $options [Option\Currency::CURRENCY] );
		
		$paymentTransaction->setRequest ( $options )->setSuccessful ( $response->isSuccessful () )->setActive ( false )->setReference ( $response->getReference () )->setResponse ( $response->getData () );
		
		$sourcePaymentTransaction->setActive ( ! $paymentTransaction->isSuccessful () );
		
		return [ 
				'message' => $response->getMessage (),
				'successful' => $response->isSuccessful () 
		];
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
		if ($sourcePaymentTransaction && $sourcePaymentTransaction->getAction () !== self::VALIDATE) {
			$sourcePaymentTransaction->setActive ( ! $paymentTransaction->isSuccessful () );
		}
		
		return [ 
				'message' => $response->success,
				'successful' => $response->success 
		];
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
			$customerData = $this->getCustomerDataPayment ( $sourcepaymenttransaction );
			$shipingData = $this->getOrderAddressPayment ( $sourcepaymenttransaction, 'shippingAddress' );
			$billingData = $this->getOrderAddressPayment ( $sourcepaymenttransaction, 'billingAddress' );

			$responseTransaction = $paymentTransaction->getResponse ();
			$request = ( array ) $paymentTransaction->getRequest ();
			
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
			
			if ( ( !empty($creditCardValue)) && ( strcmp ( $creditCardValue, "newCreditCard" ) != 0) ) {

				$paymentTransactionEntity = $this->doctrineHelper->getEntityRepository(PaymentTransaction::class)->findOneBy([
						'id' => $creditCardValue,
				]);
				

				$token = $paymentTransactionEntity->getReference();
				// Esto es para ver si el cliente exite en Braintree y sino es asi entonces le mando los datos
				try {
					$customer = $this->adapter->findCustomer ( $customerData ['id'] );
					$data = [
							'amount' => $paymentTransaction->getAmount (),
							'customerId' => $customerData ['id'], // esto cuando ya existe el cliente y tengo que dar de alta
							// una nueva tarjeta
							'billing' => $billingData,
							'shipping' => $shipingData,
					];
				} catch ( NotFound $e ) {
					$data = [
							'amount' => $paymentTransaction->getAmount (),
							'customer' => $customerData, // esto si es nuevo lo tengo que enviar
							// 'customerId' => 'the_customer_id', // esto cuando ya existe el cliente y tengo que dar de alta
							// una nueva tarjeta
							'billing' => $billingData,
							'shipping' => $shipingData,

					];
				}
				
				$response = $this->adapter->creditCardsale ($token, $data );
				if ($response->success || ! is_null ( $response->transaction )) {
					// Esto es si chage
					$transaction = $response->transaction;
						
					if ($isCharge) {
						$paymentTransaction->setAction ( self::PURCHASE )->setActive ( false )->setSuccessful ( $response->success );
					}
						
					// Esto es si authorizr
					if ($isAuthorize) {
						$transactionID = $transaction->id;
						$paymentTransaction->setAction ( self::AUTHORIZE )->setActive ( true )->setSuccessful ( $response->success );
					}
						
					$transactionOptions = $paymentTransaction->getTransactionOptions ();
					$transactionOptions ['transactionId'] = $transactionID;
					$paymentTransaction->setTransactionOptions ( $transactionOptions );
					$sourcepaymenttransaction->setActive ( false );
				} else {
					$errorString = "";
					foreach ( $response->errors->deepAll () as $error ) {
						$errorString .= $error->message . " [" . $error->code .  "]\n";
					}
					
					$paymentTransaction->setAction ( self::VALIDATE )->setActive ( false )->setSuccessful ( false );
					$sourcepaymenttransaction->setActive ( false )->setSuccessful ( false );
					
					$this->setErrorMessage($errorString);
					
					return [
						'message' => $errorString,
						'successful' => false
					];
				}				
				
			} else { // Esto siginifica que es una nueva tarjeta
				
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
					$customer = $this->adapter->findCustomer ( $customerData ['id'] );
					$data = [ 
							'amount' => $paymentTransaction->getAmount (),
							'paymentMethodNonce' => $nonce,
							'customerId' => $customerData ['id'], // esto cuando ya existe el cliente y tengo que dar de alta
							                                      // una nueva tarjeta
							'billing' => $billingData,
							'shipping' => $shipingData,
							'options' => [ 
									'submitForSettlement' => $submitForSettlement,
									'storeInVaultOnSuccess' => $storeInVaultOnSuccess 
							] 
					];
				} catch ( NotFound $e ) {
					$data = [ 
							'amount' => $paymentTransaction->getAmount (),
							'paymentMethodNonce' => $nonce,
							'customer' => $customerData, // esto si es nuevo lo tengo que enviar
							                             // 'customerId' => 'the_customer_id', // esto cuando ya existe el cliente y tengo que dar de alta
							                             // una nueva tarjeta
							'billing' => $billingData,
							'shipping' => $shipingData,
							'options' => [ 
									'submitForSettlement' => $submitForSettlement,
									'storeInVaultOnSuccess' => $storeInVaultOnSuccess 
							] 
					];
				}
				
				$response = $this->adapter->sale ( $data );
				
				if ($response->success || ! is_null ( $response->transaction )) {
					// Esto es si chage
					$transaction = $response->transaction;
					
					if ($isCharge) {
						$paymentTransaction->setAction ( self::PURCHASE )->setActive ( false )->setSuccessful ( $response->success );
					}
					
					// Esto es si authorizr
					if ($isAuthorize) {
						$transactionID = $transaction->id;
						$paymentTransaction->setAction ( self::AUTHORIZE )->setActive ( true )->setSuccessful ( $response->success );
					}
					
					$transactionOptions = $paymentTransaction->getTransactionOptions ();
					$transactionOptions ['transactionId'] = $transactionID;
					$paymentTransaction->setTransactionOptions ( $transactionOptions );
					// $paymentTransaction->setReference($reference);
					// Para la parte del token id de la tarjeta de credito
					if ($saveForLater) {
						$creditCardValuesResponse = $transaction->creditCard;
						$token = $creditCardValuesResponse ['token'];
						$paymentTransaction->setReference ( $token );
						$paymentTransaction->setResponse ( $creditCardValuesResponse );
					}
					$sourcepaymenttransaction->setActive ( false );
				} else {
					$errorString = "";
					foreach ( $response->errors->deepAll () as $error ) {
						$errorString .= $error->message . " [" . $error->code .  "]\n";
					}
					
					$paymentTransaction->setAction ( self::VALIDATE )->setActive ( false )->setSuccessful ( false );
					$sourcepaymenttransaction->setActive ( false )->setSuccessful ( false );
					
					$this->setErrorMessage($errorString);
					
					return [
						'message' => $errorString,
						'successful' => false 
					];
				}
			} // else de nuevaTarjeta de Credito
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
		
		$paymentTransaction->setSuccessful ( true )->setAction ( self::VALIDATE )->setActive ( true );
		
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
		$paymentTransaction->setSuccessful ( true )->setAction ( self::VALIDATE )->setActive ( true );
		// ->setReference($response->getReference())
		// ->setResponse($response->getData());
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
	 * @param PaymentTransaction $paymentTransaction        	
	 * @param PaymentTransaction $sourcePaymentTransaction        	
	 */
	protected function useValidateTransactionData(PaymentTransaction $paymentTransaction, PaymentTransaction $sourcePaymentTransaction) {
		$paymentTransaction->setCurrency ( $sourcePaymentTransaction->getCurrency () )->setReference ( $sourcePaymentTransaction->getReference () )->setSuccessful ( $sourcePaymentTransaction->isSuccessful () )->setActive ( $sourcePaymentTransaction->isActive () )->setRequest ()->setResponse ();
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
	 * @param PaymentTransaction $sourcepaymenttransaction
	 */
	private function getCustomerDataPayment (PaymentTransaction $sourcepaymenttransaction){

		$entityID = $sourcepaymenttransaction->getEntityIdentifier ();
		$entity = $this->doctrineHelper->getEntityReference ( $sourcepaymenttransaction->getEntityClass (), $sourcepaymenttransaction->getEntityIdentifier () );
		$propertyAccessor = $this->getPropertyAccessor ();
		
		try {
			$customerUser = $propertyAccessor->getValue ( $entity, 'customerUser' );
		} catch ( NoSuchPropertyException $e ) {
		}
		
		$userName = $customerUser->getUsername();
		
		$id = $customerUser->getId();
		if ($this->isNullDataToSend($id)){
			$id = '';
		}
		
		$firstName = $customerUser->getFirstName();
		if ($this->isNullDataToSend($firstName)){
			$firstName = '';
		}
		$lastName = $customerUser->getLastName();
		if ($this->isNullDataToSend($lastName)){
			$lastName = '';
		}
		$company = $customerUser->getOrganization()->getName();
		if ($this->isNullDataToSend($company)){
			$company = '';
		}
		$email = $customerUser->getEmail();
		if ($this->isNullDataToSend($email)){
			$email = '';
		}
		$phone = 0; // no se de donde sacarlo
		$fax = 0; // no se de donde sacarlo aun
		$website = '';
		if ($this->isNullDataToSend($website)){
			$website = '';
		}		
		$customer = array (
				'id' => $id,
				'firstName' => $firstName,
				'lastName' => $lastName,
				'company' => $company,
				'email' => $email,
				'phone' => $phone,
				'fax' => $fax,
				'website' => $website
		);

		return $customer;
	}
	
	private function getOrderAddressPayment (PaymentTransaction $sourcepaymenttransaction, $typeAddress){
		
		$entityID = $sourcepaymenttransaction->getEntityIdentifier ();
		$entity = $this->doctrineHelper->getEntityReference ( $sourcepaymenttransaction->getEntityClass (), $sourcepaymenttransaction->getEntityIdentifier () );
		$propertyAccessor = $this->getPropertyAccessor ();
		
		try {
			$orderAddress = $propertyAccessor->getValue ( $entity, $typeAddress );
		} catch ( NoSuchPropertyException $e ) {
		}
		
		
		$firstName = $orderAddress->getFirstName();
		if ($this->isNullDataToSend($firstName)){
			$firstName = '';
		}
		$lastName = $orderAddress->getLastName();
		if ($this->isNullDataToSend($lastName)){
			$lastName = '';
		}
		$company = $orderAddress->getOrganization ();
		if ($this->isNullDataToSend($company)){
			$company = '';
		}
		$streetAddress = $orderAddress->getStreet();
		if ($this->isNullDataToSend($streetAddress)){
			$streetAddress = '';
		}
		$streetAddress2 = $orderAddress->getStreet2();
		if ($this->isNullDataToSend($streetAddress2)){
			$streetAddress2 = '';
		}
		$locality = $orderAddress->getCity();
		if ($this->isNullDataToSend($locality)){
			$locality = '';
		}
		$region = $orderAddress->getRegion()->getCode();
		if ($this->isNullDataToSend($region)){
			$region = '';
		}
		$postalCode = $orderAddress->getPostalCode();
		if ($this->isNullDataToSend($postalCode)){
			$postalCode = '';
		}
		$countryName = $orderAddress->getCountry()->getName();
		if ($this->isNullDataToSend($countryName)){
			$countryName = '';
		}
		$orderReturn = array (
				'firstName' => $firstName,
				'lastName' => $lastName,
				'company' => $company,
				'streetAddress' => $streetAddress,
				'extendedAddress' => $streetAddress2,
				'locality'    => $locality,
				'region'    => $region,
				'postalCode'    => $postalCode,
				'countryName'    => $countryName
		);
		
		return $orderReturn;
		
	}
	
	/**
	 * 
	 * @param unknown $data
	 * @return boolean
	 */
	private function isNullDataToSend ($data){
		if ($data == null){
			return true;
		}
		else
			return false;
	}
	
	private function setErrorMessage($errorMessage)
	{
		$flashBag = $this->session->getFlashBag();
	
		if (!$flashBag->has('error')) {
			$flashBag->add('error', $this->translator->trans('entrepids.braintree.result.error', ['{{errorMessage}}' => $errorMessage]));
		}
	}
}