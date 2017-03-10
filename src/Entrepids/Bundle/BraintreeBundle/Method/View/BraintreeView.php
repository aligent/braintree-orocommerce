<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\View;

use Entrepids\Bundle\BraintreeBundle\Method\Braintree;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class BraintreeView implements PaymentMethodViewInterface
{
	/** @var BraintreeConfigInterface */
	private $config;

	/**
	 * @param BraintreeConfigInterface $config
	 */
	public function __construct(BraintreeConfigInterface $config)
	{
		$this->config = $config;
	}

	/** {@inheritdoc} */
	public function getOptions(PaymentContextInterface $context)
	{
		return [];
	}

	/** {@inheritdoc} */
	public function getBlock()
	{
		return '_payment_methods_braintree_widget';
	}

	/** {@inheritdoc} */
	public function getOrder()
	{
		return $this->config->getOrder();
	}

	/** {@inheritdoc} */
	public function getLabel()
	{
		return $this->config->getLabel();
	}

	/** {@inheritdoc} */
	public function getShortLabel()
	{
		return $this->config->getShortLabel();
	}

	/** {@inheritdoc} */
	public function getPaymentMethodType()
	{
		return Braintree::TYPE;
	}

	/**
	 * @return array
	 */
	public function getAllowedCreditCards()
	{
		return $this->config->getAllowedCreditCards();
	}

	/**
	 * @return array
	 */
	public function getAllowedEnvironmentTypes()
	{
		return $this->config->getAllowedEnvironmentTypes();
	}	
	/**
	 * @return string
	 */
	public function getSandBoxMerchId(){
		return $this->config->getSandBoxMerchId();
	}
	/**
	 * @return string
	*/
	public function getSandBoxMerchAccountId(){
		return $this->config->getSandBoxMerchAccountId();
	}
	/**
	 * @return string
	*/
	public function getSandBoxPublickKey(){
		return $this->config->getSandBoxPublickKey();
	}
	/**
	 * @return string
	*/
	public function getSandBoxPrivateKey(){
		return $this->config->getSandBoxPrivateKey();
	}
	/**
	 * @return bool
	 */
	public function isCreditCardEnabled(){
		return $this->config->isCreditCardEnabled();
	}
	
	/**
	 * @return string
	 */
	public function getSandBoxCreditCardTitle(){
		return $this->config->getSandBoxCreditCardTitle();
	}
}
