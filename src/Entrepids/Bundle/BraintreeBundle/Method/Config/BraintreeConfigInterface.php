<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config;

use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;
use Oro\Bundle\PaymentBundle\Method\Config\CountryConfigAwareInterface;

interface BraintreeConfigInterface extends
PaymentConfigInterface,
CountryConfigAwareInterface
{
	/**
	 * @return array
	 */
	public function getAllowedCreditCards();
	/**
	 * @return array
	 */
	public function getAllowedEnvironmentTypes();	
	/**
	 * @return string
	 */
	public function getEnvironmentSelected();	
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
	 * @return bool
	 */
	public function isCreditCardEnabled();
	/**
	 * @return string
	*/
	public function getSandBoxCreditCardTitle();
	/**
	 * @return string
	 */
	public function getPurchaseAction();
	/**
	 * @return bool
	 */
	public function isEnabledVaultSavedCards();
	/**
	 * @return bool
	 */
	public function isEnabledCvvVerification();	
	/**
	 * @return bool
	 */
	public function isDisplayCreditCard();
	/**
	 * @return bool
	 */	
	public function isEnableSafeForLater();
}