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


use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Aligent\BraintreeBundle\Method\Action\Provider\BraintreeActionProviderInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Psr\Log\LoggerInterface;

class AligentBraintreeMethod implements PaymentMethodInterface
{
    /**
     * @var BraintreeConfigInterface
     */
    protected $config;

    /**
     * @var BraintreeActionProviderInterface
     */
    protected $actionProvider;

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AligentBraintreeMethod constructor.
     * @param BraintreeConfigInterface $config
     * @param BraintreeActionProviderInterface $actionProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        BraintreeConfigInterface $config,
        BraintreeActionProviderInterface $actionProvider,
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->actionProvider = $actionProvider;
        $this->actionProvider->setConfig($this->config);
        $this->logger = $logger;
    }

    /**
     * @param string $action
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    public function execute($action, PaymentTransaction $paymentTransaction)
    {
        if (!$this->supports($action)) {
            throw new \InvalidArgumentException(sprintf('Unsupported action "%s"', $action));
        }

        try {
            $paymentTransaction->setAction($action);
            $actionInstance = $this->actionProvider->getAction($action);
            return $actionInstance->execute($paymentTransaction);
        } catch (\Exception $e) {
            $this->logger->critical(
                "Exception excuting Braintree Payment action ({$action})",
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

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    /**
     * @param PaymentContextInterface $context
     * @return bool
     */
    public function isApplicable(PaymentContextInterface $context)
    {
        return true;
    }

    /**
     * @param string $actionName
     * @return bool
     */
    public function supports($actionName)
    {
        return $this->actionProvider->hasAction($actionName);
    }
}