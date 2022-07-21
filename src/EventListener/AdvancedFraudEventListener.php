<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Jan Plank <jan.plank@aligent.com.au>
 * @copyright 2021 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\EventListener;

use Aligent\BraintreeBundle\Event\BraintreePaymentActionEvent;
use Aligent\BraintreeBundle\Method\Action\PurchaseAction;
use InvalidArgumentException;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

class AdvancedFraudEventListener
{
    /**
     * Add deviceData to payload as required for a braintree advanced fraud detection
     * https://developer.paypal.com/braintree/docs/guides/premium-fraud-management-tools/server-side
     */
    public function onPurchase(BraintreePaymentActionEvent $event): void
    {
        if ($event->getAction() !== PurchaseAction::ACTION) {
            // Ignore other action types
            return;
        }

        if ($event->getConfig()->isFraudProtectionAdvancedEnabled()) {
            $data = $event->getData();

            $paymentTransaction = $event->getPaymentTransaction();
            $data['deviceData'] = $this->getDeviceData($paymentTransaction);

            $event->setData($data);
        }
    }

    /**
     * Extracts the device data out of the additional data array
     */
    protected function getDeviceData(PaymentTransaction $paymentTransaction): string
    {
        $transactionOptions = $paymentTransaction->getTransactionOptions();

        if (!isset($transactionOptions['additionalData'])) {
            throw new InvalidArgumentException('Payment Transaction does not contain additionalData');
        }

        $additionalData = json_decode($transactionOptions['additionalData'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                "Error decoding Payment Transaction additional data Error: " . json_last_error_msg()
            );
        }

        return $additionalData['deviceData'];
    }
}
