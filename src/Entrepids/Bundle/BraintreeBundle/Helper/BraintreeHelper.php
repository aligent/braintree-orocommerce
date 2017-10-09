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
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\ExistingCreditCardPurchase;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\NewCreditCardPurchase;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Validate\OperationValidate;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Capture\OperationCapture;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Charge\OperationCharge;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Complete\OperationComplete;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Authorize\OperationAuthorize;


class BraintreeHelper {

	
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
	
	protected $genericOperation;
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
		$operationCapture = new OperationCapture($this->session, $this->translator,$this->propertyAccessor, $this->doctrineHelper, $this->adapter, $this->config);
		$this->setGenericOperation($operationCapture);
		$this->setAndExecuteOperation($paymentTransaction);
		
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function charge(PaymentTransaction $paymentTransaction) {
		$operationCharge = new OperationCharge($this->session, $this->translator,$this->propertyAccessor, $this->doctrineHelper, $this->adapter, $this->config);
		$this->setGenericOperation($operationCharge);
		$this->setAndExecuteOperation($paymentTransaction);
	}	
	
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function purchase(PaymentTransaction $paymentTransaction) {
		$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction ();
		if ($sourcepaymenttransaction != null) {
			$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction ();
			
			$transactionOptions = $sourcepaymenttransaction->getTransactionOptions ();
			$nonce = $transactionOptions ['nonce'];
			if (array_key_exists ( 'credit_card_value', $transactionOptions )) {
				$creditCardValue = $transactionOptions ['credit_card_value'];
			} else {
				$creditCardValue = "newCreditCard";
			}
			
			if ( ( !empty($creditCardValue)) && ( strcmp ( $creditCardValue, "newCreditCard" ) != 0) ) {
				$purchaseOperation = new ExistingCreditCardPurchase($this->session, $this->translator,$this->propertyAccessor, $this->doctrineHelper, $this->adapter, $this->config);
			} else {
				$purchaseOperation = new NewCreditCardPurchase($this->session, $this->translator,$this->propertyAccessor, $this->doctrineHelper, $this->adapter, $this->config);
			} // else de nuevaTarjeta de Credito
			$this->setGenericOperation($purchaseOperation);
			$this->setAndExecuteOperation($paymentTransaction);
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
		$operationValidate = new OperationValidate($this->session, $this->translator,$this->propertyAccessor, $this->doctrineHelper, $this->adapter, $this->config);
		$this->setGenericOperation($operationValidate);
		return $this->setAndExecuteOperation($paymentTransaction);
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 */
	public function complete(PaymentTransaction $paymentTransaction) {
		$operationComplete = new OperationComplete($this->session, $this->translator,$this->propertyAccessor, $this->doctrineHelper, $this->adapter, $this->config);
		$this->setGenericOperation($operationComplete);
		$this->setAndExecuteOperation($paymentTransaction);
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction
	 */
	public function authorize(PaymentTransaction $paymentTransaction) {
		$operationAuthorize = new OperationAuthorize($this->session, $this->translator,$this->propertyAccessor, $this->doctrineHelper, $this->adapter, $this->config);
		$this->setGenericOperation($operationAuthorize);
		$this->setAndExecuteOperation($paymentTransaction);
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
	
	protected function setGenericOperation ($genericOperation){
		$this->genericOperation = $genericOperation;
	}

	protected function getGenericOperation (){
		return $this->genericOperation;
	}
	
	protected function setAndExecuteOperation($paymentTransaction){
		$this->getGenericOperation()->setPaymentTransaction($paymentTransaction);
		return $this->getGenericOperation()->operationProcess();
		
	}
	
}