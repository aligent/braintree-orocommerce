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
use InvalidArgumentException;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

class AdvancedFraudEventListener
{
    /**
     * Add deviceData to payload as required for abraintree advanced fraud detection
     * https://developer.paypal.com/braintree/docs/guides/premium-fraud-management-tools/server-side
     */
    public function onPurchase(BraintreePaymentActionEvent $actionEvent): void
    {
        if ($actionEvent->getConfig()->isFraudProtectionAdvancedEnabled()) {
            $data = $actionEvent->getData();

            $paymentTransaction = $actionEvent->getPaymentTransaction();
            $data['deviceData'] = $this->getDeviceData($paymentTransaction);

            $actionEvent->setData($data);
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
