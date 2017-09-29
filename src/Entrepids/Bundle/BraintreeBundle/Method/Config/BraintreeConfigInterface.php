<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config;

use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;
//use Oro\Bundle\PaymentBundle\Method\Config\CountryConfigAwareInterface;

interface BraintreeConfigInterface extends
PaymentConfigInterface
{
	/**
	 * @return array
	 */
	public function getAllowedCreditCards();
	/**
	 * @return string
	 */
	public function getAllowedEnvironmentTypes();	
	/**
	 * @return string
	 */
	public function getSandBoxMerchId();	
	/**
	 * @return string
	 */
	public function getSandBoxMerchAccountId();	
	/**
	 * @return string
	 */
	public function getSandBoxPublickKey();	
	/**
	 * @return string
	 */
	public function getSandBoxPrivateKey();	
	/**
	 * @return string
	 */
	public function getPurchaseAction();
	/**
	 * @return bool
	 */	
	public function isEnableSaveForLater();
	/**
	 * @return bool
	 */
	public function isZeroAmountAuthorizationEnabled();	
	
	/**
	 * @return string
	 */
	public function getPaymentMethodNonce();

	/**
	 * @return string
	 */
	public function getBraintreeClientToken();

	/**
	 * @return string
	 */
	public function getCreditCardValue();	

	/**
	 * @return string
	 */
	public function getCreditCardsSaved();
}