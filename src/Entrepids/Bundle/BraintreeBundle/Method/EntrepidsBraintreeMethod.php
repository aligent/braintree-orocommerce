<?php

namespace Entrepids\Bundle\BraintreeBundle\Method;

use Entrepids\Bundle\BraintreeBundle\Helper\BraintreeHelper;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Factory;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseData;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseOperation;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

class EntrepidsBraintreeMethod implements
    PaymentMethodInterface
{

    const TYPE = 'entrepids_braintree';

    /** @var BraintreeConfig */
    private $config;

    /** @var Factory */
    protected $opFactory;

    /**
     * @param BraintreeConfig $config
     */
    public function __construct(
        Factory $opFactory,
        BraintreeConfig $config
    ) {
        $this->config = $config;
        $this->opFactory = $opFactory;
    }


    /**
     * {@inheritdoc}
     */
    public function execute($action, PaymentTransaction $paymentTransaction)
    {
        if (!$this->supports($action)) {
            throw new \InvalidArgumentException(sprintf('Unsupported action "%s"', $action));
        }

        try {
            /** @var AbstractBraintreeOperation $operation */
            $operation = $this->opFactory->getOperation($action);
            $operation->setConfig($this->config)
                ->operationProcess($paymentTransaction);
        } catch (\Exception $e) {
            $paymentTransaction->setAction($this->paymentOperation)
                ->setActive(false)
                ->setSuccessful(false);
            $paymentTransaction->getSourcePaymentTransaction()
                ->setActive(false)
                ->setSuccessful(false);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(PaymentContextInterface $context)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($actionName)
    {
        return in_array((string)$actionName, [
            self::VALIDATE,
            self::CAPTURE,
            self::CHARGE,
            self::PURCHASE,
        ], true);
    }


    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }

}
