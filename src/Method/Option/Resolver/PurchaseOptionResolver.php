<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/11/19
 * Time: 2:46 AM
 */

namespace Aligent\BraintreeBundle\Method\Option\Resolver;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

class PurchaseOptionResolver implements OptionResolverInterface
{

    /**
     * @param PaymentTransaction $paymentTransaction
     * @param BraintreeConfigInterface $config
     * @return array
     */
    public function resolveOptions(PaymentTransaction $paymentTransaction, BraintreeConfigInterface $config)
    {
        $transactionOptions = $paymentTransaction->getTransactionOptions();
        $additionalData = json_decode($transactionOptions['additionalData'], true);

        if (!isset($additionalData['nonce'])) {
            throw new \InvalidArgumentException('Payment Nonce is missing!');
        }

        $nonce = $additionalData['nonce'];


        return array_merge(

        );
    }
}