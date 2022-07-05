<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\Action;

use Aligent\BraintreeBundle\Braintree\Gateway;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Braintree\Result\Error;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

class PurchaseAction extends AbstractBraintreeAction
{
    const ACTION = 'purchase';

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return mixed
     */
    public function execute(PaymentTransaction $paymentTransaction)
    {
        $data = $this->buildRequestData($paymentTransaction);
        $paymentTransaction->setRequest($data);

        try {
            $response = $this->braintreeGateway->sale($data);
        } catch (\Exception $exception) {
            $this->handleException($paymentTransaction, $exception);
            $this->setPaymentTransactionStateFailed($paymentTransaction);
            return [
                'successful' => false
            ];
        }

        if ($response instanceof Error) {
            $this->handleError($paymentTransaction, $response);
            $this->setPaymentTransactionStateFailed($paymentTransaction);

            return [
                'successful' => false
            ];
        }

        $paymentTransaction
            ->setResponse((array) $response)
            ->setSuccessful($response->success)
            ->setActive(false);

        return [
            'successful' => $response->success
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::ACTION;
    }
}
