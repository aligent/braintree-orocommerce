<?php
/**
 *
 *
 * @category  Aligent
 * @package
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2019 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\EventListener;

use Aligent\BraintreeBundle\Event\BraintreePaymentActionEvent;

class PurchaseActionEventListener
{
    /**
     * @param BraintreePaymentActionEvent $actionEvent
     */
    public function onPurchase(BraintreePaymentActionEvent $actionEvent)
    {
        $data = $actionEvent->getData();

        // Purchase transactions we want to submit for settlement immediately
        $data['options'] = [
            'submitForSettlement' => true
        ];

        // merchantAccountId is what determines the currency, if not set it will use the accounts default
        $data['merchantAccountId'] = $actionEvent->getConfig()->getMerchantAccountId();

        $paymentTransaction = $actionEvent->getPaymentTransaction();
        $customerUser = $paymentTransaction->getFrontendOwner();

        // If this is a vaulted customer set their customer ID on the transaction
        if ($customerUser && $braintreeId = $customerUser->getBraintreeId()) {
            $data['customerId'] = $braintreeId;
        }

        $actionEvent->setData($data);
    }
}