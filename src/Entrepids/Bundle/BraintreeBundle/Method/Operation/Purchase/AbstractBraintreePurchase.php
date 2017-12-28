<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase;

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
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use phpDocumentor\Reflection\Types\Array_;
use BeSimple\SoapCommon\Type\KeyValue\Boolean;

/**
 * This is abstract class that contains generic methods for the purchase operation
 */
abstract class AbstractBraintreePurchase extends AbstractBraintreeOperation
{

    /**
     *
     * @var Array_
     */
    protected $customerData;

    /**
     *
     * @var Array_
     */
    protected $billingData;

    /**
     *
     * @var Array_
     */
    protected $shipingData;

    /**
     *
     * @var unknown
     */
    protected $identifier;

    /**
     *
     * @var Boolean
     */
    protected $isCharge;

    /**
     *
     * @var Boolean
     */
    protected $isAuthorize;

    /**
     * This method is used to obtain response from Braintree
     */
    abstract protected function getResponseFromBraintree();

    /**
     * This method is used to set data success to Oro
     */
    abstract protected function processSuccess($response);

    /**
     * This method check or set variables needs to preprocess response
     */
    abstract protected function setDataToPreProcessResponse();

    protected function saveResponseSuccessData($response)
    {
        $transaction = $response->transaction;
        
        $creditCardDetails = $transaction->creditCardDetails;
        $transactionOptions = $this->paymentTransaction->getTransactionOptions();
        $transactionOptions['creditCardDetails'] = serialize($creditCardDetails);
        $transactionOptions['isBraintreeEntrepids'] = true;
        $this->paymentTransaction->setTransactionOptions($transactionOptions);
    }

    /**
     * This method is used to process the response of braintree core
     *
     * @param unknown $response
     */
    protected function processResponseBriantee($response)
    {
        $this->setDataToPreProcessResponse();
        
        if ($response->success && ! is_null($response->transaction)) {
            $this->saveResponseSuccessData($response);
            $this->processSuccess($response);
        } else {
            $this->processError($response);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::postProcessOperation()
     */
    protected function postProcessOperation()
    {
        $response = $this->getResponseFromBraintree();
        $this->processResponseBriantee($response);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preprocessDataToSend()
     */
    protected function preprocessDataToSend()
    {
        $paymentTransaction = $this->paymentTransaction;
        $sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction();
        
        $transactionOptions = $sourcepaymenttransaction->getTransactionOptions();
        
        if (array_key_exists('credit_card_value', $transactionOptions)) {
            $creditCardValue = $transactionOptions['credit_card_value'];
        } else {
            $creditCardValue = "newCreditCard";
        }
        
        $this->customerData = $this->getCustomerDataPayment($sourcepaymenttransaction);
        $this->shipingData = $this->getOrderAddressPayment($sourcepaymenttransaction, 'shippingAddress');
        $this->billingData = $this->getOrderAddressPayment($sourcepaymenttransaction, 'billingAddress');
        
        $responseTransaction = $paymentTransaction->getResponse();
        $request = (array) $paymentTransaction->getRequest();
        
        $entity = $this->doctrineHelper->getEntityReference(
            $paymentTransaction->getEntityClass(),
            $paymentTransaction->getEntityIdentifier()
        );
        
        $orderID = $entity->getId();
        $this->identifier = $entity->getIdentifier();
    }

    /**
     * This method set the error from braintree responses
     *
     * @param unknown $response
     */
    protected function processError($response)
    {
        $errorString = "";
        $erroProcessed = false;
        foreach ($response->errors->deepAll() as $error) {
            $errorString .= $error->message . " [" . $error->code . "]\n";
            $erroProcessed = true;
        }
        
        $errorMessage = "";
        if (! $erroProcessed && ! is_null($response->message)) {
            $errorString = $response->message;
        }
        
        $this->paymentTransaction->setAction(PaymentMethodInterface::VALIDATE)
            ->setActive(false)
            ->setSuccessful(false);
        $this->paymentTransaction->getSourcePaymentTransaction()
            ->setActive(false)
            ->setSuccessful(false);
        
        $this->setErrorMessage($errorString);
        
        return [
            'message' => $errorString,
            'successful' => false
        ];
    }

    /**
     * This is a method to obtain the data of customer user to send to braintree
     *
     * @param PaymentTransaction $sourcepaymenttransaction
     */
    protected function getCustomerDataPayment(PaymentTransaction $sourcepaymenttransaction)
    {
        $entityID = $sourcepaymenttransaction->getEntityIdentifier();
        $entity = $this->doctrineHelper->getEntityReference(
            $sourcepaymenttransaction->getEntityClass(),
            $sourcepaymenttransaction->getEntityIdentifier()
        );
        $propertyAccessor = $this->getPropertyAccessor();
        
        try {
            $customerUser = $propertyAccessor->getValue($entity, 'customerUser');
        } catch (NoSuchPropertyException $e) {
        }
        
        $userName = $customerUser->getUsername();
        
        $id = $customerUser->getId();
        if ($this->isNullDataToSend($id)) {
            $id = '';
        }
        
        $firstName = $customerUser->getFirstName();
        if ($this->isNullDataToSend($firstName)) {
            $firstName = '';
        }
        $lastName = $customerUser->getLastName();
        if ($this->isNullDataToSend($lastName)) {
            $lastName = '';
        }
        $company = $customerUser->getOrganization()->getName();
        if ($this->isNullDataToSend($company)) {
            $company = '';
        }
        $email = $customerUser->getEmail();
        if ($this->isNullDataToSend($email)) {
            $email = '';
        }
        $phone = 0;
        $fax = 0;
        $website = '';
        if ($this->isNullDataToSend($website)) {
            $website = '';
        }
        $customer = array(
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

    /**
     * This method obtain the address depending of typeAddress
     *
     * @param PaymentTransaction $sourcepaymenttransaction
     * @param unknown $typeAddress
     */
    protected function getOrderAddressPayment(PaymentTransaction $sourcepaymenttransaction, $typeAddress)
    {
        $entityID = $sourcepaymenttransaction->getEntityIdentifier();
        $entity = $this->doctrineHelper->getEntityReference(
            $sourcepaymenttransaction->getEntityClass(),
            $sourcepaymenttransaction->getEntityIdentifier()
        );
        $propertyAccessor = $this->getPropertyAccessor();
        
        try {
            $orderAddress = $propertyAccessor->getValue($entity, $typeAddress);
        } catch (NoSuchPropertyException $e) {
        }
        
        $firstName = $this->setNameOrderAddress($orderAddress);
        
        $lastName = $this->setLastName($orderAddress);
        
        $company = $this->setCompany($orderAddress);
        
        $streetAddress = $this->setStreet1($orderAddress);
        $streetAddress2 = $this->setStreet2($orderAddress);
        $locality = $this->setLocality($orderAddress);
        $region = $this->setRegion($orderAddress);
        $postalCode = $this->setCodePostal($orderAddress);
        $countryName = $this->setCountryName($orderAddress);
        
        $orderReturn = array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'company' => $company,
            'streetAddress' => $streetAddress,
            'extendedAddress' => $streetAddress2,
            'locality' => $locality,
            'region' => $region,
            'postalCode' => $postalCode,
            'countryName' => $countryName
        );
        
        return $orderReturn;
    }

    /**
     * Set Country Name
     *
     * @param unknown $orderAddress
     */
    private function setCountryName($orderAddress)
    {
        if ($this->isNullDataToSend($orderAddress->getCountry()
            ->getName())) {
            return '';
        }
        
        return $orderAddress->getCountry()->getName();
    }

    /**
     * Set Code Postal
     *
     * @param unknown $orderAddress
     */
    private function setCodePostal($orderAddress)
    {
        if ($this->isNullDataToSend($orderAddress->getPostalCode())) {
            return '';
        }
        
        return $orderAddress->getPostalCode();
    }

    /**
     * Set Region
     *
     * @param unknown $orderAddress
     */
    private function setRegion($orderAddress)
    {
        if ($this->isNullDataToSend($orderAddress->getRegion()
            ->getCode())) {
            return '';
        }
        
        return $orderAddress->getRegion()->getCode();
    }

    /**
     * Set first name
     *
     * @param unknown $orderAddress
     */
    private function setNameOrderAddress($orderAddress)
    {
        if ($this->isNullDataToSend($orderAddress->getFirstName())) {
            return '';
        }
        
        return $orderAddress->getFirstName();
    }

    /**
     * Set last name
     *
     * @param unknown $orderAddress
     */
    private function setLastName($orderAddress)
    {
        if ($this->isNullDataToSend($orderAddress->getLastName())) {
            return '';
        }
        
        return $orderAddress->getLastName();
    }

    /**
     * set Company
     *
     * @param unknown $orderAddress
     */
    private function setCompany($orderAddress)
    {
        if ($this->isNullDataToSend($orderAddress->getOrganization())) {
            return '';
        }
        
        return $orderAddress->getOrganization();
    }

    /**
     * Set Street address 1
     *
     * @param unknown $orderAddress
     */
    private function setStreet1($orderAddress)
    {
        if ($this->isNullDataToSend($orderAddress->getStreet())) {
            return '';
        }
        
        return $orderAddress->getStreet();
    }

    /**
     * Set Street address 2
     *
     * @param unknown $orderAddress
     */
    private function setStreet2($orderAddress)
    {
        if ($this->isNullDataToSend($orderAddress->getStreet2())) {
            return '';
        }
        
        return $orderAddress->getStreet2();
    }

    /**
     * Set Locality
     *
     * @param unknown $orderAddress
     */
    private function setLocality($orderAddress)
    {
        if ($this->isNullDataToSend($orderAddress->getCity())) {
            return '';
        }
        
        return $orderAddress->getCity();
    }

    /**
     * This is function to check if data is or not null
     *
     * @param unknown $data
     * @return boolean
     */
    private function isNullDataToSend($data)
    {
        if ($data == null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function add error to flash bag
     *
     * @param unknown $errorMessage
     */
    private function setErrorMessage($errorMessage)
    {
        $flashBag = $this->session->getFlashBag();
        
        if (! $flashBag->has('error')) {
            $flashBag->add('error', $this->translator->trans('entrepids.braintree.result.error', [
                '{{errorMessage}}' => $errorMessage
            ]));
        }
    }
}
