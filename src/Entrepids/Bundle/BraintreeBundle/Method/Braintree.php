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
use Entrepids\Bundle\BraintreeBundle\Helper\BraintreeHelper;


class Braintree implements PaymentMethodInterface {
	const TYPE = 'braintree';
	const COMPLETE = 'complete';
	
	/**
	 * @var BraintreeConfigInterface
	 */
	private $config;
	

	
	/**
	 *
	 * @param BraintreeConfigInterface $config        	    	
	 * @param BraintreeAdapter $adapter        	
	 * @param DoctrineHelper $doctrineHelper        	   	
	 * @param PropertyAccessor $propertyAccessor        	
	 */
	public function __construct(BraintreeConfigInterface $config, DoctrineHelper $doctrineHelper, 
			PropertyAccessor $propertyAccessor, Session $session, TranslatorInterface $translator) {
		$this->config = $config;
		$this->braintreeHelper = new BraintreeHelper($config, $doctrineHelper, $propertyAccessor, $session, $translator);
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
		$this->braintreeHelper->capture($paymentTransaction);
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction        	
	 * @return array
	 */
	public function charge(PaymentTransaction $paymentTransaction) {
	$this->braintreeHelper->charge($paymentTransaction);
	}
	

	/**
	 *
	 * @param PaymentTransaction $paymentTransaction        	
	 * @return array
	 */
	public function purchase(PaymentTransaction $paymentTransaction) {
		$this->braintreeHelper->purchase($paymentTransaction);
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction        	
	 * @return array
	 */
	public function validate(PaymentTransaction $paymentTransaction) {
		$this->braintreeHelper->validate($paymentTransaction);
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction        	
	 */
	public function complete(PaymentTransaction $paymentTransaction) {
		$this->braintreeHelper->complete($paymentTransaction);
	}
	
	/**
	 *
	 * @param PaymentTransaction $paymentTransaction        	
	 */
	public function authorize(PaymentTransaction $paymentTransaction) {
		$this->braintreeHelper->authorize($paymentTransaction);
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
	

}