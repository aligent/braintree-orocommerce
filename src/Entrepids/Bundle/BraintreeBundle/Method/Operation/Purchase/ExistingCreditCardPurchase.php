<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase;

use Braintree\Exception\NotFound;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeCustomerToken;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseData;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

class ExistingCreditCardPurchase extends AbstractBraintreePurchase
{

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Helper\AbstractBraintreePurchase::getResponseFromBraintree()
     */
    protected function getResponseFromBraintree()
    {
        $paymentTransaction = $this->paymentTransaction;
        $sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction();
        
        $transactionOptions = $sourcepaymenttransaction->getTransactionOptions();
        if (array_key_exists('credit_card_value', $transactionOptions)) {
            $creditCardValue = $transactionOptions['credit_card_value'];
        } else {
            $creditCardValue = PurchaseData::NEWCREDITCARD;
        }
        
        $sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction();

        if ($creditCardValue != PurchaseData::NEWCREDITCARD) {
            $token = $this->getTransactionCustomerToken($creditCardValue);
        } else {
            $token = null;
        }

        $merchAccountID = $this->config->getBoxMerchAccountId();
        // ORO REVIEW:
        // Please, see
        // \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\NewCreditCardPurchase::getResponseFromBraintree
        // comments
        try {
            $customer = $this->adapter->findCustomer($this->customerData['id']);
            $data = [
                'amount' => $paymentTransaction->getAmount(),
                'channel' => 'OroCommerceBT_SP',
                'customerId' => $this->customerData['id'],
                'billing' => $this->billingData,
                'shipping' => $this->shipingData,
                'orderId' => $this->identifier,
                'merchantAccountId' => $merchAccountID
            ];
        } catch (NotFound $e) {
            $data = [
                'amount' => $paymentTransaction->getAmount(),
                'channel' => 'OroCommerceBT_SP',
                'customer' => $this->customerData,
                'billing' => $this->billingData,
                'shipping' => $this->shipingData,
                'orderId' => $this->identifier,
                'merchantAccountId' => $merchAccountID
            ];
        }
        
        $response = $this->adapter->creditCardsale($token, $data);
        return $response;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\AbstractBraintreePurchase::setDataToPreProcessResponse()
     */
    protected function setDataToPreProcessResponse()
    {
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
        
        $this->paymentTransaction->getSourcePaymentTransaction()->setActive(false);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Helper\AbstractBraintreePurchase::preProcessPurchase()
     */
    protected function preProcessOperation()
    {
        $purchaseAction = $this->config->getPurchaseAction();

        $this->isAuthorize = ($purchaseAction == PaymentMethodInterface::AUTHORIZE);
        $this->isCharge = ($purchaseAction == PaymentMethodInterface::CHARGE);
    }
    
    /**
     * The method get the customer token to determine if they have any saved card
     */
    private function getTransactionCustomerToken($transaction)
    {
        $customerTokens = $this->doctrineHelper->getEntityRepository(BraintreeCustomerToken::class)->findOneBy([
            'transaction' => $transaction
        ]);
    
        return $customerTokens->getToken();
    }
}
