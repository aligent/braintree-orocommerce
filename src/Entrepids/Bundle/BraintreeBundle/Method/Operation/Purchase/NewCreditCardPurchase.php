<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase;

use Braintree\Exception\NotFound;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeCustomerToken;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

class NewCreditCardPurchase extends AbstractBraintreePurchase
{

    /**
     *
     * @var String
     */
    protected $nonce;

    /**
     *
     * @var Boolean
     */
    protected $submitForSettlement;

    /**
     *
     * @var Boolean
     */
    protected $saveForLater;

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Helper\AbstractBraintreePurchase::getResponseFromBraintree()
     */
    protected function getResponseFromBraintree()
    {
        $sourcepaymenttransaction = $this->paymentTransaction->getSourcePaymentTransaction();
        $transactionOptions = $sourcepaymenttransaction->getTransactionOptions();
        $saveForLater = false;

        if (array_key_exists('saveForLaterUse', $transactionOptions)) {
            $saveForLater = $transactionOptions['saveForLaterUse'];
        }
        $storeInVaultOnSuccess = false;
        if ($saveForLater) {
            $storeInVaultOnSuccess = true;
        } else {
            $storeInVaultOnSuccess = false;
        }

        $merchAccountID = $this->config->getBoxMerchAccountId();
        try {
            $customer = $this->adapter->findCustomer($this->customerData['id']);
            $data = [
                'amount' => $this->paymentTransaction->getAmount(),
                'paymentMethodNonce' => $this->nonce,
                // ORO REVIEW:
                // What is the pupropose of this constant? Why it is not saved into php constant,
                // or into specific configuration?
                'channel' => 'OroCommerceBT_SP',
                'customerId' => $this->customerData['id'],
                'billing' => $this->billingData,
                'shipping' => $this->shipingData,
                'orderId' => $this->identifier,
                'merchantAccountId' => $merchAccountID,
                'options' => [
                    'submitForSettlement' => $this->submitForSettlement,
                    'storeInVaultOnSuccess' => $storeInVaultOnSuccess,
                ],
            ];
        } catch (NotFound $e) {
            // ORO REVIEW:
            // It really hard to understand what is the difference from `$data` variable in `try` block.
            // Please, refactor without the copy paste.
            // As I see `$this->customerData` cannot contain only an array.
            // Is this block was tested?
            $data = [
                'amount' => $this->paymentTransaction->getAmount(),
                'paymentMethodNonce' => $this->nonce,
                'channel' => 'OroCommerceBT_SP',
                'customer' => $this->customerData,
                'billing' => $this->billingData,
                'shipping' => $this->shipingData,
                'orderId' => $this->identifier,
                'merchantAccountId' => $merchAccountID,
                'options' => [
                    'submitForSettlement' => $this->submitForSettlement,
                    'storeInVaultOnSuccess' => $storeInVaultOnSuccess,
                ],
            ];
        }

        $response = $this->adapter->sale($data);

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

        if ($this->isCharge) {
            $this->paymentTransaction->setAction(PaymentMethodInterface::PURCHASE)
                ->setActive(false)
                ->setSuccessful($response->success);
        }

        if ($this->isAuthorize) {
            $transactionID = $transaction->id;
            $this->paymentTransaction->setAction(PaymentMethodInterface::AUTHORIZE)
                ->setActive(true)
                ->setSuccessful($response->success);

            $transactionOptions = $this->paymentTransaction->getTransactionOptions();
            $transactionOptions['transactionId'] = $transactionID;
            $this->paymentTransaction->setTransactionOptions($transactionOptions);
        }

        if ($this->saveForLater) {
            $creditCardValuesResponse = $transaction->creditCard;
            $token = $creditCardValuesResponse['token'];
            $this->paymentTransaction->setResponse($creditCardValuesResponse);

            $this->saveCustomerToken($token);
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
        $paymentTransaction = $this->paymentTransaction;
        $sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction();

        $transactionOptions = $sourcepaymenttransaction->getTransactionOptions();
        $nonce = $transactionOptions['nonce'];

        $purchaseAction = $this->config->getPurchaseAction();
        $submitForSettlement = true;
        $isAuthorize = false;
        $isCharge = false;
        // ORO REVIEW:
        // This logic is copy pasted.
        // src/Entrepids/Bundle/BraintreeBundle/Method/Operation/Purchase/ExistingCreditCardPurchase.php
        if ($purchaseAction == PaymentMethodInterface::AUTHORIZE) {
            $submitForSettlement = false;
            $isAuthorize = true;
        }
        if ($purchaseAction == PaymentMethodInterface::CHARGE) {
            $submitForSettlement = true;
            $isCharge = true;
        }

        $this->submitForSettlement = $submitForSettlement;
        $this->nonce = $nonce;
        $this->isAuthorize = $isAuthorize;
        $this->isCharge = $isCharge;
    }

    /**
     * This function save the customer and token to BraintreeCustomerToken
     *
     * @param string $token
     */
    private function saveCustomerToken($token)
    {
        $customerToken = new BraintreeCustomerToken();

        $entityID = $this->paymentTransaction->getEntityIdentifier();
        $entity = $this->doctrineHelper->getEntityReference(
            $this->paymentTransaction->getEntityClass(),
            $this->paymentTransaction->getEntityIdentifier()
        );
        $propertyAccessor = $this->getPropertyAccessor();

        try {
            $customerUser = $propertyAccessor->getValue($entity, 'customerUser');
            $userName = $customerUser->getUsername();
            $customerId = $customerUser->getId();
            $customerToken->setCustomer($customerId);
            $customerToken->setToken($token);
            $paymentTransactionId = $this->paymentTransaction->getSourcePaymentTransaction()->getId();
            $customerToken->setTransaction($paymentTransactionId);
            $em = $this->doctrineHelper->getEntityManager(BraintreeCustomerToken::class);
            $em->persist($customerToken);
            $em->flush();
        } catch (NoSuchPropertyException $e) {
        }
    }
}
