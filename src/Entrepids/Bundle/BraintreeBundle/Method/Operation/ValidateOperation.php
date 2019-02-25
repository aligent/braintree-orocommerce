<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation;

use Entrepids\Bundle\BraintreeBundle\Method\EntrepidsBraintreeMethod;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use Entrepids\Bundle\BraintreeBundle\Method\Provider\BraintreeMethodProvider;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ValidateOperation extends AbstractBraintreeOperation
{
    const ZERO_AMOUNT = 0;

    /** @var RequestStack */
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    /**
     * @inheritDoc
     */
    protected function postProcessOperation()
    {
        $request = $this->requestStack->getCurrentRequest();

        $paymentTransaction = $this->paymentTransaction;
        $paymentTransaction->setAmount(self::ZERO_AMOUNT)->setCurrency('USD');
        $transactionOptions = $paymentTransaction->getTransactionOptions();
        $requestData = [
            static::CREDIT_CARD_VALUE_KEY => BraintreeMethodProvider::NEWCREDITCARD,
            static::NONCE_KEY => null
        ];
        // Fetch the nonce and credit_card_value from the request and merge with the transaction options
        // By recursively iterating over the request data to extract the needed values
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($request->request->all()),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $key => $value) {
            if ($key === static::NONCE_KEY) {
                $requestData[static::NONCE_KEY] = $value;
            } elseif ($key === static::CREDIT_CARD_VALUE_KEY) {
                $requestData[static::CREDIT_CARD_VALUE_KEY] = $value;
            }
        }

        $transactionOptions = array_merge($transactionOptions, $requestData);
        $paymentTransaction->setTransactionOptions($transactionOptions);
        $paymentTransaction->setSuccessful(true)
            ->setAction(PaymentMethodInterface::VALIDATE)
            ->setActive(true);

        return [];
    }
}
