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

        // Fetch the nonce and credit_card_value from the request and merge with the transaction options
        $requestData = $request->get(
            'oro_workflow_transition',
            [
                static::NONCE_KEY => null,
                static::CREDIT_CARD_VALUE_KEY => BraintreeMethodProvider::NEWCREDITCARD
            ]
        );

        $extraData = array_filter(
            $requestData,
            function ($key) {
                return $key === static::CREDIT_CARD_VALUE_KEY
                    || $key === static::NONCE_KEY;
            },
            ARRAY_FILTER_USE_KEY
        );



        $transactionOptions = array_merge($transactionOptions, $extraData);
        $paymentTransaction->setTransactionOptions($transactionOptions);
        $paymentTransaction->setSuccessful(true)
            ->setAction(PaymentMethodInterface::VALIDATE)
            ->setActive(true);

        return [];
    }
}
