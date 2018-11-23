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

    public function __construct(RequestStack $requestStack) {
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

        $transactionOptions = array_merge($transactionOptions, [
            'credit_card_value' => $request->get('credit_card_value', BraintreeMethodProvider::NEWCREDITCARD),
            'nonce' => $request->get('payment_method_nonce', null),
        ]);

        $paymentTransaction->setTransactionOptions($transactionOptions);

        $paymentTransaction->setSuccessful(true)
            ->setAction(PaymentMethodInterface::VALIDATE)
            ->setActive(true);

        return [];
    }
}
