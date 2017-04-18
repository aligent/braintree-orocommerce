<?php

namespace Entrepids\Bundle\BraintreeBundle\Method;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Oro\Bundle\PaymentBundle\Provider\ExtractOptionsProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Provider\SurchargeProvider;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Braintree implements PaymentMethodInterface
{
	const TYPE = 'braintree';
	
	const COMPLETE = 'complete';
	
	const ZERO_AMOUNT = 0;

	/** @var BraintreeConfigInterface */
	private $config;
	
	/** @var RouterInterface */
	protected $router;
	
	/**
	 * @var BraintreeAdapter
	 */
	protected $adapter;	
	
	/** @var DoctrineHelper */
	protected $doctrineHelper;
	
	/** @var ExtractOptionsProvider */
	protected $optionsProvider;
	
	/** @var PropertyAccessor */
	protected $propertyAccessor;
	
	/** @var SurchargeProvider */
	protected $surchargeProvider;
	
	/**
	 * 
	 * @param BraintreeConfigInterface $config
	 * @param RouterInterface $router
	 * @param BraintreeAdapter $adapter
	 * @param DoctrineHelper $doctrineHelper
	 * @param ExtractOptionsProvider $optionsProvider
	 * @param SurchargeProvider $surchargeProvider
	 * @param PropertyAccessor $propertyAccessor
	 */
	public function __construct(BraintreeConfigInterface $config,
			RouterInterface $router, 
			BraintreeAdapter $adapter, 
        DoctrineHelper $doctrineHelper,
        ExtractOptionsProvider $optionsProvider,
        SurchargeProvider $surchargeProvider,
        PropertyAccessor $propertyAccessor
        )
	{
		$this->config = $config;
		$this->router = $router;
		$this->adapter = $adapter;
        $this->doctrineHelper = $doctrineHelper;
        $this->optionsProvider = $optionsProvider;
        $this->surchargeProvider = $surchargeProvider;
        $this->propertyAccessor = $propertyAccessor;
	}

	/** {@inheritdoc} */
	public function execute($action, PaymentTransaction $paymentTransaction)
	{
		if (!$this->supports($action)) {
			throw new \InvalidArgumentException(sprintf('Unsupported action "%s"', $action));
		}
		
		$purchaseAction = $this->config->getPurchaseAction();
		
		
/*		switch ($action) {
			case self::PURCHASE:
				$paymentTransaction
				->setAction(self::AUTHORIZE)
				->setActive(true)
				->setSuccessful(true);
				break;
			case self::CAPTURE:
				$paymentTransaction
				->setActive(false)
				->setSuccessful(true);

				$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
				if ($sourcePaymentTransaction) {
					$sourcePaymentTransaction->setActive(false);
				}
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Action %s not supported', $action));
		}*/

		return $this->{$action}($paymentTransaction) ?: [];
	}

	/** {@inheritdoc} */
	public function getType()
	{
		return self::TYPE;
	}

	/** {@inheritdoc} */
	public function isEnabled()
	{
		return $this->config->isEnabled();
	}

	/** {@inheritdoc} */
	public function isApplicable(PaymentContextInterface $context)
	{
		/*return $this->config->isCountryApplicable($context)
		 && $this->config->isCurrencyApplicable($context);*/
		return true;
	}

	/** {@inheritdoc} */
	public function supports($actionName)
	{
		if ($actionName === self::VALIDATE) {
			return true;
		}
		
		return in_array((string)$actionName, [self::AUTHORIZE, self::CAPTURE, self::CHARGE, self::PURCHASE, self::COMPLETE], true);
	}
	
	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function capture(PaymentTransaction $paymentTransaction)
	{
		$options = $this->getPaymentOptions($paymentTransaction);
		$paymentTransaction->setRequest($options);
		//Aca tengo que obtener el transactionID y realizar la llamada a Braintree mediante el adapter
		$purchaseAction = $this->config->getPurchaseAction();
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();

		$this->setExtraDataPurchase ( $sourcePaymentTransaction);
		// me fijo por las dudas si esta en modo authorize, aunque no se bien...
		$isAuthorize=false;
		if (strcmp("authorize", $purchaseAction) == 0){
			$isAuthorize=true;
			// hacer lo que tenga que hacer si esta en modo authorize
		}
		
		// aca hacer la llamada a Braintree con el id que supuestamente se genero,
		// sino hay ID entonces preguntar que hacer
		
		if (!$sourcePaymentTransaction) { // esto estaba original de la copia de PAYPAL
			$paymentTransaction
			->setSuccessful(false)
			->setActive(false);
		
			return ['successful' => false];
		}
		
		if ($sourcePaymentTransaction->isClone()) { // esto es original de la copia de PAYPAL
			return $this->charge($paymentTransaction);
		}

		// aca va si no es un clone???
		$response = $this->gateway
		->request(Option\Transaction::DELAYED_CAPTURE, $this->combineOptions($options)); // esto es original y tengo que ver 
		// que va en la respuesta o es lo que devuelve el metodo charge
		
		unset($options[Option\Currency::CURRENCY]);
		
		
		$paymentTransaction
		->setRequest($options)
		->setSuccessful($response->isSuccessful())
		->setActive(false)
		->setReference($response->getReference())
		->setResponse($response->getData());
		
		$sourcePaymentTransaction->setActive(!$paymentTransaction->isSuccessful());
		
		return [
				'message' => $response->getMessage(),
				'successful' => $response->isSuccessful(),
		];		
	}
	
	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function charge(PaymentTransaction $paymentTransaction)
	{
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
		
		$transactionOptions = $sourcePaymentTransaction->getTransactionOptions();

		if (array_key_exists('transactionId', $transactionOptions)) {
			$id = $transactionOptions['transactionId'];
		}
		else {
			$id = null;
		}
		

		
		if ($id != null){ // si existe el id de la transaccion entonces
			return $this->setPaymentCaptureChargeData ( $paymentTransaction, $sourcePaymentTransaction, $id );
			
		}
		else{ // no existe el id de la transaccion
			// dejo la transaccion y la orden como estaba??
			return [
					'message' => 'No transaction Id',
					'successful' => false,
			];			
		}

	}
	/**
	 * @param paymentTransaction
	 * @param sourcePaymentTransaction
	 * @param transactionData
	 * @param status
	 */private function setPaymentCaptureChargeData($paymentTransaction, $sourcePaymentTransaction, $id) {
		$response = $this->adapter->submitForSettlement($id);
		
		if (!$response->success){
			$errors = $response->message;
			$transactionData = $response->transaction;
			$status = $transactionData->__get('status');
			
			if (strcmp($status, Braintree\Transaction::AUTHORIZED)==0){ //esto es lo que dice la clase Transaction del modulo Braintree
				// es estado authorizado y fallo
				$paymentTransaction
				->setSuccessful($response->success)
				->setActive(true);
				//->setReference($response->getReference()) // no estoy seguro, lo saco hasta que sepa que va
				//->setResponse($response->getData()); // ni idea que puede ser data, lo saco hasta que sepa

			}
			else{
				// es otro estado y fallo, aca tengo que poner la transaccion que ya fue capturada previamente
				$paymentTransaction
				->setSuccessful(true) // lo pongo en true porque no es estado authorized
				->setActive(false);
				//->setReference($response->getReference()) // no estoy seguro, lo saco hasta que sepa que va
				//->setResponse($response->getData()); // ni idea que puede ser data, lo saco hasta que sepa
			}
		
		}
		else{
			$errors = 'No errors';
			$paymentTransaction
			->setSuccessful($response->success)
			->setActive(false);
			//->setReference($response->getReference()) // no estoy seguro, lo saco hasta que sepa que va
			//->setResponse($response->getData()); // ni idea que puede ser data, lo saco hasta que sepa

				
		}
		
		if ($sourcePaymentTransaction) {
			$paymentTransaction->setActive(false);
		}
		if ($sourcePaymentTransaction && $sourcePaymentTransaction->getAction() !== self::VALIDATE) {
			$sourcePaymentTransaction->setActive(!$paymentTransaction->isSuccessful());
		}
		
		return [
				'message' => $response->success,
				'successful' => $response->success,
		];
	}
	
	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function purchase(PaymentTransaction $paymentTransaction)
	{
		// Aca cambiar por El Adapter o como lo hace Magento
		//$nonce = $this->adapter->createNonce('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		//$this->adapter->find('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction();
		if ($sourcepaymenttransaction != null){
			$transactionOptions = $sourcepaymenttransaction->getTransactionOptions();
			$nonce = $transactionOptions['nonce'];
			$responseTransaction = $paymentTransaction->getResponse();
			$request = (array)$paymentTransaction->getRequest();
			
			$purchaseAction = $this->config->getPurchaseAction();
			// authorize or charge
			// si charge mando true
			// si authorize mando false
			$submitForSettlement = true;
			$isAuthorize=false;
			$isCharge=false;
			if (strcmp("authorize", $purchaseAction) == 0){
				$submitForSettlement = false;
				$isAuthorize=true;
			}
			if (strcmp("charge", $purchaseAction) == 0){
				$submitForSettlement = true;
				$isCharge=true;
			}			
			
			$this->setExtraDataPurchase ( $sourcepaymenttransaction);

			
			/*
			 * esta es la maner a de enviar los datos hacia Braintree
			   'customer' => [
			    'firstName' => 'Drew',
			    'lastName' => 'Smith',
			    'company' => 'Braintree',
			    'phone' => '312-555-1234',
			    'fax' => '312-555-1235',
			    'website' => 'http://www.example.com',
			    'email' => 'drew@example.com'
			  ],
			  'billing' => [
			    'firstName' => 'Paul',
			    'lastName' => 'Smith',
			    'company' => 'Braintree',
			    'streetAddress' => '1 E Main St',
			    'extendedAddress' => 'Suite 403',
			    'locality' => 'Chicago',
			    'region' => 'IL',
			    'postalCode' => '60622',
			    'countryCodeAlpha2' => 'US'
			  ],
			  'shipping' => [
			    'firstName' => 'Jen',
			    'lastName' => 'Smith',
			    'company' => 'Braintree',
			    'streetAddress' => '1 E 1st St',
			    'extendedAddress' => 'Suite 403',
			    'locality' => 'Bartlett',
			    'region' => 'IL',
			    'postalCode' => '60103',
			    'countryCodeAlpha2' => 'US'
			  ],
			 */
			$data = [
					'amount' => $paymentTransaction->getAmount(),
					'paymentMethodNonce' => $nonce,
					'options' => [
							'submitForSettlement' => $submitForSettlement
					]
			];
			$response = $this->adapter->sale($data);
			
			if ($response->success || !is_null($response->transaction)) {
				// Esto es si chage
				$transaction = $response->transaction;
				
				if ($isCharge){
					$paymentTransaction
					->setAction(self::PURCHASE)
					->setActive(false)
					->setSuccessful($response->success);
				}
				
				//Esto es si authorizr
				if ($isAuthorize){
					$transactionID = $transaction->id;
					$paymentTransaction
					->setAction(self::AUTHORIZE)
					->setActive(true)
					->setSuccessful($response->success);
					

					
				}
				
				$transactionOptions = $paymentTransaction->getTransactionOptions();
				$transactionOptions['transactionId'] = $transactionID;
				$paymentTransaction->setTransactionOptions($transactionOptions);
				
				$sourcepaymenttransaction
				->setActive(false);

			} else {
				$errorString = "";
			
				foreach($response->errors->deepAll() as $error) {
					$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
				}
				$paymentTransaction
				->setAction(self::VALIDATE)
				->setActive(false)
				->setSuccessful(false);
			
			}
		}
		

		


	}
	
	/**
	 * @param sourcepaymenttransaction
	 */private function setExtraDataPurchase($sourcepaymenttransaction) {
		// revisar de enviar los datos de Customer, Billing, Shipping
		// primero como los obtengo de Oro via backend
		$owner = $sourcepaymenttransaction->getOwner();
		$organization =  $sourcepaymenttransaction->getOrganization();
		$entityID = $sourcepaymenttransaction->getEntityIdentifier();
		$entity = $this->doctrineHelper->getEntityReference(
				$sourcepaymenttransaction->getEntityClass(),
				$sourcepaymenttransaction->getEntityIdentifier()
		);
		$propertyAccessor = $this->getPropertyAccessor();

		try {
		    $shippingAddress = $propertyAccessor->getValue($entity, 'shippingAddress');
		} catch (NoSuchPropertyException $e) {
		    
		}

		if (!$shippingAddress instanceof AbstractAddress) {
		   
		}

		$class = $this->doctrineHelper->getEntityClass($shippingAddress);
		$addressOption = $this->optionsProvider->getShippingAddressOptions($class, $shippingAddress);
	}

	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function validate(PaymentTransaction $paymentTransaction)
	{
        $paymentTransaction
            ->setAmount(self::ZERO_AMOUNT)
            ->setCurrency('USD');

/*        $options = array_merge(
            $this->getPaymentOptions($paymentTransaction),
        	[]
            //$this->getSecureTokenOptions($paymentTransaction)
        );

        $paymentTransaction
            ->setRequest($options)
            ->setAction(PaymentMethodInterface::VALIDATE);

        $this->authorize($paymentTransaction);*/

        $nonce = $_POST["payment_method_nonce"];
        $transactionOptions = $paymentTransaction->getTransactionOptions();
        $transactionOptions['nonce'] = $nonce;
        $paymentTransaction->setTransactionOptions($transactionOptions);
        
        $paymentTransaction
        ->setSuccessful(true)
        ->setAction(self::VALIDATE)
        ->setActive(true);
        
        return [];
        //return $this->secureTokenResponse($paymentTransaction);
	}

	/**
	 * @param PaymentTransaction $paymentTransaction
	 */
	public function complete(PaymentTransaction $paymentTransaction)
	{
		if ($paymentTransaction->getAction() === PaymentMethodInterface::CHARGE) {
			$paymentTransaction->setActive(false);
		}
	}	
	
	/**
	 * @param PaymentTransaction $paymentTransaction
	 */
	public function authorize(PaymentTransaction $paymentTransaction)
	{
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
		if ($sourcePaymentTransaction) {
			$this->useValidateTransactionData($paymentTransaction, $sourcePaymentTransaction);
	
			return;
		}
	
		// Aca cambiar por El Adapter o como lo hace Magento
		//$nonce = $this->adapter->createNonce('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		//$this->adapter->find('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		$nonce = $_POST["payment_method_nonce"];
		$transactionOptions = $paymentTransaction->getTransactionOptions();
		$transactionOptions['nonce'] = $nonce;
		$paymentTransaction->setTransactionOptions($transactionOptions);
		/*$nonce = $_POST["payment_method_nonce"];
		$responseTransaction = $paymentTransaction->getResponse();
		$request = (array)$paymentTransaction->getRequest();
		$data = [ 
				'amount' => 145,
				'paymentMethodNonce' => $nonce,
				'options' => [ 
						'submitForSettlement' => true 
				] 
		];
		$response = $this->adapter->sale($data);
		
		if ($response->success || !is_null($response->transaction)) {
			$transaction = $response->transaction;
		} else {
			$errorString = "";
		
			foreach($response->errors->deepAll() as $error) {
				$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
			}
		
		}
*/
		$paymentTransaction
		->setSuccessful(true)
		->setAction(self::VALIDATE)
		->setActive(true);
		//->setReference($response->getReference())
		//->setResponse($response->getData());
	}
	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	protected function getPaymentOptions(PaymentTransaction $paymentTransaction)
	{
		$options = [
				'AMT' => round($paymentTransaction->getAmount(), 2),
				'TENDER' => 'C',
				'CURRENCY' => $paymentTransaction->getCurrency(),
		];
	
		if ($paymentTransaction->getSourcePaymentTransaction()) {
			$options['ORIGID'] =
			$paymentTransaction->getSourcePaymentTransaction()->getReference();
		}
	
		return $options;
	}	
	
	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @param PaymentTransaction $sourcePaymentTransaction
	 */
	protected function useValidateTransactionData(
			PaymentTransaction $paymentTransaction,
			PaymentTransaction $sourcePaymentTransaction
	) {
		$paymentTransaction
		->setCurrency($sourcePaymentTransaction->getCurrency())
		->setReference($sourcePaymentTransaction->getReference())
		->setSuccessful($sourcePaymentTransaction->isSuccessful())
		->setActive($sourcePaymentTransaction->isActive())
		->setRequest()
		->setResponse();
	}
	
	/**
	 * @return PropertyAccessor
	 */
	protected function getPropertyAccessor()
	{
		return $this->propertyAccessor;
	}
}