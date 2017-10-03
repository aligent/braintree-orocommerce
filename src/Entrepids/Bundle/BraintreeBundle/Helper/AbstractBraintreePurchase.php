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

abstract class AbstractBraintreePurchase implements PurchaseInterface {
	
	protected $customerData;
	
	protected $billingData;
	
	protected $shipingData;
	
	protected $identifier;
	
	protected $isCharge;
	
	protected $isAuthorize;
	
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

	public function setPaymentTransaction(PaymentTransaction $paymentTransaction ){
		$this->paymentTransaction = $paymentTransaction;
	
	}
	
	public function getPaymentTransaction(){
		return $this->paymentTransaction;
	}
	

	public function processError($response){
	
		$errorString = "";
		foreach ( $response->errors->deepAll () as $error ) {
			$errorString .= $error->message . " [" . $error->code .  "]\n";
		}
	
		$this->paymentTransaction->setAction ( PaymentMethodInterface::VALIDATE )->setActive ( false )->setSuccessful ( false );
		$this->paymentTransaction->getSourcePaymentTransaction()->setActive ( false )->setSuccessful ( false );
	
		$this->setErrorMessage($errorString);
	
		return [
				'message' => $errorString,
				'successful' => false
		];
	
	}	
	
	/**
	 *
	 * @param PaymentTransaction $sourcepaymenttransaction
	 */
	protected function getCustomerDataPayment (PaymentTransaction $sourcepaymenttransaction){
	
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
	
	protected function getOrderAddressPayment (PaymentTransaction $sourcepaymenttransaction, $typeAddress){
	
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
	 * @return PropertyAccessor
	 */
	protected function getPropertyAccessor() {
		return $this->propertyAccessor;
	}
	
	abstract protected function preProcessPurchase ();
	abstract protected function postProcessPurchase ();
	
	public function processPurchase (){
		$this->preProcessPurchase();
		$this->postProcessPurchase();
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