<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method;

use Aligent\BraintreeBundle\Method\Action\Provider\BraintreeActionProviderInterface;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Psr\Log\LoggerInterface;

class AligentBraintreeMethod implements PaymentMethodInterface
{
    protected BraintreeConfigInterface $config;
    protected BraintreeActionProviderInterface $actionProvider;
    protected LoggerInterface $logger;

    public function __construct(
        BraintreeConfigInterface $config,
        BraintreeActionProviderInterface $actionProvider,
        LoggerInterface $logger,
    ) {
        $this->config = $config;
        $this->actionProvider = $actionProvider;
        $this->logger = $logger;
    }

    /**
     * @param string $action
     * @param PaymentTransaction $paymentTransaction
     * @return array<string,mixed>
     */
    public function execute($action, PaymentTransaction $paymentTransaction): array
    {
        if (!$this->supports($action)) {
            throw new \InvalidArgumentException(sprintf('Unsupported action "%s"', $action));
        }

        try {
            $paymentTransaction->setAction($action);
            $actionInstance = $this->actionProvider->getAction($action);
            $actionInstance->initialize($this->config);
            return $actionInstance->execute($paymentTransaction);
        } catch (\Exception $e) {
            $this->logger->critical(
                "Exception executing Braintree Payment action ({$action})",
                [
                    'payment_transaction_id' => $paymentTransaction->getId(),
                    'payment_method' => $paymentTransaction->getPaymentMethod(),
                    'message' => $e->getMessage(),
                    'exception' => $e
                ]
            );

            $paymentTransaction
                ->setSuccessful(false)
                ->setActive(false);
        }

        return [
            'successful' => false
        ];
    }

    public function getIdentifier(): string
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    public function isApplicable(PaymentContextInterface $context): bool
    {
        return true;
    }

    /**
     * @param string $actionName
     * @return bool
     */
    public function supports($actionName): bool
    {
        return $this->actionProvider->hasAction($actionName);
    }
}
