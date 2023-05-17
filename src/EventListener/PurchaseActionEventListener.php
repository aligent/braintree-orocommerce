<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\EventListener;

use Aligent\BraintreeBundle\Event\BraintreePaymentActionEvent;
use Aligent\BraintreeBundle\Method\Action\PurchaseAction;
use Oro\Bundle\AddressBundle\Entity\AddressType;

class PurchaseActionEventListener
{
    public function onPurchase(BraintreePaymentActionEvent $event): void
    {
        if ($event->getAction() !== PurchaseAction::ACTION) {
            // Ignore other action types
            return;
        }

        $data = $event->getData();

        // Purchase transactions we want to submit for settlement immediately
        $data['options'] = [
            'submitForSettlement' => true
        ];

        // merchantAccountId is what determines the currency, if not set it will use the accounts default
        $data['merchantAccountId'] = $event->getConfig()->getMerchantAccountId();

        $paymentTransaction = $event->getPaymentTransaction();
        $customerUser = $paymentTransaction->getFrontendOwner();

        // If this is a vaulted customer set their customer ID on the transaction
        /** @phpstan-ignore-next-line The FrontendOwner of a PaymentTransaction is actually nullable */
        if ($customerUser
            && method_exists($customerUser, 'getBraintreeId')
            && $braintreeId = $customerUser->getBraintreeId()
        ) {
            $data['customerId'] = $braintreeId;
        }

        //Add oro order Id to braintree data
        $data['orderId'] = (string) $paymentTransaction->getEntityIdentifier();

        $billing = $customerUser->getAddressByTypeName(AddressType::TYPE_BILLING);
        $address = $billing->getStreet();
        if ($billing->getStreet2()) {
            $address .= ', ' . $billing->getStreet2();
        }
        $data['billing'] = [
            'firstName' => $billing->getFirstName(),
            'lastName' => $billing->getLastName(),
            'streetAddress' => $address,
            'region' => $billing->getRegion(),
            'locality' => $billing->getCity(),
            'postalCode' => $billing->getPostalCode(),
            'countryCodeAlpha2' => $billing->getCountryIso2(),
        ];
        if ($billing->getOrganization()) {
            $data['billing']['company'] = $billing->getOrganization();
        }

        $event->setData($data);
    }
}
