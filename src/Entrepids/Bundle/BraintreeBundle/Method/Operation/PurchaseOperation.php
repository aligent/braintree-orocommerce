<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation;

use Braintree\Customer;
use Braintree\Exception\NotFound;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeCustomerToken;
use Entrepids\Bundle\BraintreeBundle\Method\Provider\BraintreeMethodProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PurchaseOperation
 *
 * @package Entrepids\Bundle\BraintreeBundle\Method\Operation
 * @SuppressWarnings(ExcessiveClassComplexity)
 */
class PurchaseOperation extends AbstractBraintreeOperation
{
    const CHANNEL_CODE = 'OroCommerce';

    /** @var String */
    protected $nonce;

    /** @var boolean */
    protected $submitForSettlement;

    /** @var boolean */
    protected $saveForLater;


    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var Session */
    protected $session;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /**
     * @param Session $session
     * @param TranslatorInterface $translator
     * @param PropertyAccessor $propertyAccessor
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        Session $session,
        TranslatorInterface $translator,
        PropertyAccessor $propertyAccessor,
        DoctrineHelper $doctrineHelper
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->propertyAccessor = $propertyAccessor;
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Helper\AbstractBraintreePurchase::getResponseFromBraintree()
     */
    protected function getResponseFromBraintree()
    {
        $sourcepaymenttransaction = $this->paymentTransaction->getSourcePaymentTransaction();
        $transactionOptions = $sourcepaymenttransaction->getTransactionOptions();

        if (array_key_exists('credit_card_value', $transactionOptions)) {
            $creditCardValue = $transactionOptions['credit_card_value'];
        } else {
            $creditCardValue = BraintreeMethodProvider::NEWCREDITCARD;
        }

        $saveForLater = false;
        if (array_key_exists('saveForLaterUse', $transactionOptions)) {
            $saveForLater = $transactionOptions['saveForLaterUse'];
        }
        $storeInVaultOnSuccess = $saveForLater;

        if ($creditCardValue != BraintreeMethodProvider::NEWCREDITCARD) {
            $token = $this->getCustomerToken($creditCardValue, $this->customerData['id']);
        } else {
            $token = null;
        }

        $data = [
            'amount' => $this->paymentTransaction->getAmount(),
            'channel' => self::CHANNEL_CODE,
            'billing' => $this->billingData,
            'shipping' => $this->shipingData,
            'orderId' => $this->identifier,
            'merchantAccountId' => $this->config->getBoxMerchAccountId(),
            'options' => [
                'submitForSettlement' => $this->submitForSettlement,
                'storeInVaultOnSuccess' => $storeInVaultOnSuccess,
            ],
        ];

        try {
            // Try to find the customer, if they aren't found then pass their entire
            // details for vaulting.  Discard the return value, just want to know if
            // it throws an exception or not.
            $this->adapter->findCustomer($this->customerData['id']);
            $data = array_merge($data, [
                'customerId' => $this->customerData['id'],
            ]);
        } catch (NotFound $e) {
            $data = array_merge($data, [
                'customer' => $this->customerData,
            ]);
        }

        if ($this->nonce !== 'noValue') {
            $data['paymentMethodNonce'] = $this->nonce;
        }

        if ($creditCardValue != BraintreeMethodProvider::NEWCREDITCARD) {
            $response = $this->adapter->creditCardsale($token, $data);
        } else {
            $response = $this->adapter->sale($data);
        }

        return $response;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase::setDataToPreProcessResponse()
     */
    protected function setDataToPreProcessResponse()
    {
        $sourcepaymenttransaction = $this->paymentTransaction->getSourcePaymentTransaction();
        $transactionOptions = $sourcepaymenttransaction->getTransactionOptions();
        $saveForLater = false;
        if (array_key_exists('saveForLaterUse', $transactionOptions)) {
            $saveForLater = $transactionOptions['saveForLaterUse'];
        }

        $this->saveForLater = $saveForLater;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase::processSuccess()
     */
    protected function processSuccess($response)
    {
        $transaction = $response->transaction;

        if ($this->isAuthorize) {
            $transactionID = $transaction->id;
            $this->paymentTransaction->setAction(PaymentMethodInterface::AUTHORIZE)
                ->setActive(true)
                ->setSuccessful($response->success);

            $transactionOptions = $this->paymentTransaction->getTransactionOptions();
            $transactionOptions['transactionId'] = $transactionID;
            $this->paymentTransaction->setTransactionOptions($transactionOptions);
        }

        if ($this->isCharge) {
            $this->paymentTransaction->setAction(PaymentMethodInterface::PURCHASE)
                ->setActive(false)
                ->setSuccessful($response->success);
        }

        if ($this->saveForLater) {
            $creditCardValuesResponse = $transaction->creditCard;
            $token = $creditCardValuesResponse['token'];
            $this->paymentTransaction->setResponse($creditCardValuesResponse);

            $this->saveCustomerToken($token, $creditCardValuesResponse);
        }
        $this->paymentTransaction->getSourcePaymentTransaction()->setActive(false);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Helper\AbstractBraintreePurchase::preProcessPurchase()
     */
    protected function preProcessOperation()
    {
        $sourcepaymenttransaction = $this->paymentTransaction->getSourcePaymentTransaction();

        $transactionOptions = $sourcepaymenttransaction->getTransactionOptions();
        $this->nonce = $transactionOptions['nonce'];

        $purchaseAction = $this->config->getPurchaseAction();

        $this->submitForSettlement = $purchaseAction != PaymentMethodInterface::AUTHORIZE;
        $this->isAuthorize = true;
        $this->isCharge = $purchaseAction == PaymentMethodInterface::CHARGE;
    }

    /**
     * The method get the customer token to determine if they have any saved card
     */
    private function getCustomerToken($tokenId, $customerId)
    {
        $customerToken = $this->doctrineHelper->getEntityRepository(BraintreeCustomerToken::class)->findOneBy(
            [
                'id' => $tokenId,
                'customer' => $customerId
            ]
        );
        return $customerToken->getToken();
    }


    /**
     * This function save the customer and token to BraintreeCustomerToken
     *
     * @param string $token
     */
    private function saveCustomerToken($token, $ccValues)
    {
        if ($token === null) {
            return;
        }

        $ccDisplayText = $this->translator->trans('entrepids.braintree.braintreeflow.existing_card', [
            '{{brand}}' => $ccValues['cardType'],
            '{{last4}}' => $ccValues['last4'],
            '{{month}}' => $ccValues['expirationMonth'],
            '{{year}}' => $ccValues['expirationYear'],
        ]);

        $tokenObj = new BraintreeCustomerToken();

        try {
            $entity = $this->doctrineHelper->getEntityReference(
                $this->paymentTransaction->getEntityClass(),
                $this->paymentTransaction->getEntityIdentifier()
            );
            $propertyAccessor = $this->getPropertyAccessor();
            $customerUser = $propertyAccessor->getValue($entity, 'customerUser');
            $customerId = $customerUser->getId();

            $tokenObj->setCustomer($customerId)
                ->setToken($token)
                ->setDisplayText($ccDisplayText);

            $em = $this->doctrineHelper->getEntityManager(BraintreeCustomerToken::class);
            $em->persist($tokenObj);
            $em->flush();
        } catch (NoSuchPropertyException $e) {
        }
    }

    protected function saveResponseSuccessData($response)
    {
        $transaction = $response->transaction;

        $creditCardDetails = $transaction->creditCardDetails;
        $transactionOptions = $this->paymentTransaction->getTransactionOptions();
        $transactionOptions['creditCardDetails'] = serialize($creditCardDetails);
        $transactionOptions['isBraintreeEntrepids'] = true;
        $this->paymentTransaction->setTransactionOptions($transactionOptions);
        if (isset($transaction->id)) {
            $this->paymentTransaction->setReference($transaction->id);
        }
    }

    /**
     * This method is used to process the response of braintree core
     *
     * @param unknown $response
     */
    protected function processResponseBriantee($response)
    {
        $this->setDataToPreProcessResponse();

        if ($response->success && !is_null($response->transaction)) {
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
            $creditCardValue = BraintreeMethodProvider::NEWCREDITCARD;
        }

        $this->customerData = $this->getCustomerDataPayment($sourcepaymenttransaction);
        $this->shipingData = $this->getOrderAddressPayment($sourcepaymenttransaction, 'shippingAddress');
        $this->billingData = $this->getOrderAddressPayment($sourcepaymenttransaction, 'billingAddress');

        $entity = $this->doctrineHelper->getEntityReference(
            $paymentTransaction->getEntityClass(),
            $paymentTransaction->getEntityIdentifier()
        );

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

        if (!$erroProcessed && !is_null($response->message)) {
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
            'successful' => false,
        ];
    }

    /**
     * This is a method to obtain the data of customer user to send to braintree
     *
     * @param PaymentTransaction $sourcepaymenttransaction
     */
    protected function getCustomerDataPayment(PaymentTransaction $sourcepaymenttransaction)
    {
        $entity = $this->doctrineHelper->getEntityReference(
            $sourcepaymenttransaction->getEntityClass(),
            $sourcepaymenttransaction->getEntityIdentifier()
        );
        $propertyAccessor = $this->getPropertyAccessor();
        $customerUser = $propertyAccessor->getValue($entity, 'customerUser');

        return [
            'id' => $customerUser->getId() ?: '',
            'firstName' => $customerUser->getFirstName() ?: '',
            'lastName' => $customerUser->getLastName() ?: '',
            'company' => $customerUser->getOrganization()->getName() ?: '',
            'email' => $customerUser->getEmail() ?: '',
            'phone' => '',
            'fax' => '',
            'website' => '',
        ];
    }

    /**
     * This method obtain the address depending of typeAddress
     *
     * @param PaymentTransaction $sourcepaymenttransaction
     * @param unknown $typeAddress
     */
    protected function getOrderAddressPayment(PaymentTransaction $sourcepaymenttransaction, $typeAddress)
    {
        $entity = $this->doctrineHelper->getEntityReference(
            $sourcepaymenttransaction->getEntityClass(),
            $sourcepaymenttransaction->getEntityIdentifier()
        );
        $propertyAccessor = $this->getPropertyAccessor();

        $orderAddress = $propertyAccessor->getValue($entity, $typeAddress);

        return [
            'firstName' => $orderAddress->getFirstName() ?: '',
            'lastName' => $orderAddress->getLastName() ?: '',
            'company' => $orderAddress->getOrganization() ?: '',
            'streetAddress' => $orderAddress->getStreet() ?: '',
            'extendedAddress' => $orderAddress->getStreet2() ?: '',
            'locality' => $orderAddress->getCity() ?: '',
            'region' => $orderAddress->getRegion()->getCode() ?: '',
            'postalCode' => $orderAddress->getPostalCode() ?: '',
            'countryName' => $orderAddress->getCountry()->getName() ?: '',
        ];
    }


    /**
     * This function add error to flash bag
     *
     * @param unknown $errorMessage
     */
    private function setErrorMessage($errorMessage)
    {
        $flashBag = $this->session->getFlashBag();

        if (!$flashBag->has('error')) {
            $flashBag->add('error', $this->translator->trans('entrepids.braintree.result.error', [
                '{{errorMessage}}' => $errorMessage,
            ]));
        }
    }

    /**
     *
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        return $this->propertyAccessor;
    }
}
