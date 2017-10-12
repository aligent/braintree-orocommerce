<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation;

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
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Interfaces\OperationInterface;

abstract class AbstractBraintreeOperation implements OperationInterface {
	
	protected $doctrineHelper;
	
	protected $adapter;
	
	protected $paymentTransaction;
	
	protected $config;
	
	/** @var Session */
	protected $session;
	
	protected $translator;	
	/**
	 * @var PropertyAccessor
	 */
	protected $propertyAccessor;	

    /**
     * 
     * @param Session $session
     * @param TranslatorInterface $translator
     * @param PropertyAccessor $propertyAccessor
     * @param DoctrineHelper $doctrineHelper
     * @param BraintreeAdapter $braintreeAdapter
     * @param BraintreeConfigInterface $config
     */
	public function __construct(Session $session, TranslatorInterface $translator, PropertyAccessor $propertyAccessor, DoctrineHelper $doctrineHelper, BraintreeAdapter $braintreeAdapter, BraintreeConfigInterface $config ){
		$this->doctrineHelper = $doctrineHelper;
		$this->adapter = $braintreeAdapter;
		$this->config = $config;
		$this->propertyAccessor = $propertyAccessor;
		$this->session = $session;
		$this->translator = $translator;
	}	
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Interfaces\OperationInterface::setPaymentTransaction()
	 */
	public function setPaymentTransaction(PaymentTransaction $paymentTransaction ){
		$this->paymentTransaction = $paymentTransaction;
	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Interfaces\OperationInterface::getPaymentTransaction()
	 */
	public function getPaymentTransaction(){
		return $this->paymentTransaction;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Interfaces\OperationInterface::operationPurchase()
	 */
	public function operationProcess (){
		//Aca se preparan los datos que luego van a ser procesados y enviados a Braintree
		$this->preprocessDataToSend();
		$this->preProcessOperation();
		
		// Esta parte se encarga de la lógica en donde se envia y recibe la respuesta de Braintree
		return $this->postProcessOperation();
	}
	
	/**
	 * 
	 */
	abstract protected function preProcessOperation ();

	/**
	 * 
	 */
	abstract protected function postProcessOperation ();
	/**
	 * 
	 */
	abstract protected function preprocessDataToSend ();



	/**
	 *
	 * @return PropertyAccessor
	 */
	protected function getPropertyAccessor() {
		return $this->propertyAccessor;
	}
	

}