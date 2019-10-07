<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/4/19
 * Time: 2:14 AM
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
     * @param BraintreeConfigInterface $config
     * @return mixed
     */
    public function execute(PaymentTransaction $paymentTransaction, BraintreeConfigInterface $config)
    {
        try {
            $options = $this->optionResolver
                ->resolveOptions(
                    $paymentTransaction,
                    $config
                );
            $gateway = Gateway::getInstance($config);
            $response = $gateway->sale($options);
        } catch (\Exception $e) {
            $this->logger->critical(
                $e->getMessage(),
                [
                    'exception' => $e,
                    'payment_transaction' => $paymentTransaction->getId(),
                    'payment_method' => $config->getPaymentMethodIdentifier()
                ]
            );

            $paymentTransaction
                ->setSuccessful(false)
                ->setActive(false);

            return [
                'successful' => false
            ];
        }

        if ($response instanceof Error) {
            $this->logger->error(
                '',
                [
                    'payment_transaction' => $paymentTransaction->getId(),
                    'payment_method' => $config->getPaymentMethodIdentifier(),
                    'response' => $response
                ]
            );
        }

        $paymentTransaction
            ->setSuccessful($response->success)
            ->setActive(false);

        return [
            'successful' => $response->success
        ];
    }
}