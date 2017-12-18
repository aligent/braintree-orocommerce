<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config;

use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;

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

    // ORO REVIEW:
    // Why do the next fields have "sandbox" prefix in the name?
    // It will be used for production mode also.
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

    // ORO REVIEW:
    // Next methods violates Open/closed principle.
    // They don't relate to this interface,
    // and was added only for using config object as data for form, for some reason
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
	public function getCreditCardFirstValue();	

	/**
	 * @return string
	 */
	public function getCreditCardsSaved();
}
