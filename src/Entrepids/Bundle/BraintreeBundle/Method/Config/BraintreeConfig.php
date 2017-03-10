<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config;

use Entrepids\Bundle\BraintreeBundle\DependencyInjection\Configuration;
use Entrepids\Bundle\BraintreeBundle\Method\Braintree;
use Oro\Bundle\PaymentBundle\DependencyInjection\Configuration as PaymentConfiguration;
use Oro\Bundle\PaymentBundle\Method\Config\AbstractPaymentConfig;
use Oro\Bundle\PaymentBundle\Method\Config\CountryAwarePaymentConfigTrait;

class BraintreeConfig extends AbstractPaymentConfig implements BraintreeConfigInterface
{
	use CountryAwarePaymentConfigTrait;

	/** {@inheritdoc} */
	protected function getPaymentExtensionAlias()
	{
		return Braintree::TYPE;
	}

	/** {@inheritdoc} */
	public function isEnabled()
	{
		return (bool)$this->getConfigValue(Configuration::BRAINTREE_ENABLED_KEY);
	}

	/** {@inheritdoc} */
	public function getOrder()
	{
		return (int)$this->getConfigValue(Configuration::BRAINTREE_SORT_ORDER_KEY);
	}

	/** {@inheritdoc} */
	public function getLabel()
	{
		return (string)$this->getConfigValue(Configuration::BRAINTREE_LABEL_KEY);
	}

	/** {@inheritdoc} */
	public function getShortLabel()
	{
		return (string)$this->getConfigValue(Configuration::BRAINTREE_SHORT_LABEL_KEY);
	}

	/** {@inheritdoc} */
	public function isAllCountriesAllowed()
	{
		return $this->getConfigValue(Configuration::BRAINTREE_ALLOWED_COUNTRIES_KEY)
		=== PaymentConfiguration::ALLOWED_COUNTRIES_ALL;
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedCountries()
	{
		return (array)$this->getConfigValue(Configuration::BRAINTREE_SELECTED_COUNTRIES_KEY);
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedCurrencies()
	{
		return (array)$this->getConfigValue(Configuration::BRAINTREE_ALLOWED_CURRENCIES);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedCreditCards()
	{
		return (array)$this->getConfigValue(Configuration::BRAINTREE_PRO_ALLOWED_CC_TYPES_KEY);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAllowedEnvironmentTypes()
	{
		return (array)$this->getConfigValue(Configuration::BRAINTREE_ENVIRONMENT_TYPES);
	}	
	/**
	 * {@inheritdoc}
	 */
	public function getSandBoxMerchId(){
		return (string)$this->getConfigValue(Configuration::BRAINTREE_SANDBOX_MERCH_ID);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getSandBoxMerchAccountId(){
		return (string)$this->getConfigValue(Configuration::BRAINTREE_SANDBOX_ACCOUNT_ID);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getSandBoxPublickKey(){
		return (string)$this->getConfigValue(Configuration::BRAINTREE_SANDBOX_PUBLIC_KEY);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getSandBoxPrivateKey(){
		return (string)$this->getConfigValue(Configuration::BRAINTREE_SANDBOX_PRIVATE_KEY);
	}
	/** {@inheritdoc} */
	public function isCreditCardEnabled()
	{
		return (bool)$this->getConfigValue(Configuration::BRAINTREE_CREDIT_CARD_ENABLED);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getSandBoxCreditCardTitle(){
		return (string)$this->getConfigValue(Configuration::BRAINTREE_CREDIT_CARD_TITLE);
	}	
	
	/**
	 * {@inheritdoc}
	 */
	public function getPurchaseAction(){
		return (string)$this->getConfigValue(Configuration::BRAINTREE_CAPTURE_PAYMENT_ACTION);
	}
	/**
	 * {@inheritdoc}
	 */
	public function isEnabledVaultSavedCards(){
		return (bool)$this->getConfigValue(Configuration::BRAINTREE_FEATURES_ENABLED_VAULT_SAVED_CARDS);
	}
	/**
	 * {@inheritdoc}
	 */
	public function isEnabledCvvVerification(){
		return (bool)$this->getConfigValue(Configuration::BRAINTREE_FEATURES_CVV_VERIFICATION);
	}
	/**
	 * {@inheritdoc}
	 */
	public function isDisplayCreditCard(){
		return (bool)$this->getConfigValue(Configuration::BRAINTREE_FEATURES_DISPLAY_CARD_TYPES);
	}
}