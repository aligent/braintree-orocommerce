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

use Aligent\BraintreeBundle\Event\BraintreePaymentActionEvent;
use Aligent\BraintreeBundle\Braintree\Gateway;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Braintree\Result\Error;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractBraintreeAction implements BraintreeActionInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected Gateway $braintreeGateway;
    protected EventDispatcherInterface $eventDispatcher;
    protected DoctrineHelper $doctrineHelper;
    protected BraintreeConfigInterface $config;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DoctrineHelper $doctrineHelper
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrineHelper = $doctrineHelper;
    }

    public function initialize(BraintreeConfigInterface $braintreeConfig): void
    {
        $this->config = $braintreeConfig;
        $this->braintreeGateway = new Gateway($braintreeConfig, $this->doctrineHelper);
    }

    protected function setPaymentTransactionStateFailed(PaymentTransaction $paymentTransaction): void
    {
        $paymentTransaction
            ->setSuccessful(false)
            ->setActive(false);
    }

    /**
     * Logs error
     */
    protected function handleError(PaymentTransaction $paymentTransaction, Error $error): void
    {
        $errorContext = $this->getErrorLogContext($paymentTransaction);
        $errorContext['error'] = $error->jsonSerialize();

        $errorMessage = sprintf(
            'Payment %s failed. Reason: %s',
            $this->getName(),
            (string) $error
        );

        $this->logError($errorMessage, $errorContext);
    }

    /**
     * Logs error
     */
    protected function handleException(PaymentTransaction $paymentTransaction, \Throwable $exceptionOrError): void
    {
        $errorMessage = sprintf(
            'Payment %s failed. Reason: %s',
            $this->getName(),
            $exceptionOrError->getMessage()
        );

        $errorContext = $this->getErrorLogContext($paymentTransaction);
        $errorContext['exception'] = $exceptionOrError;

        $this->logError($errorMessage, $errorContext);
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return array<string,mixed>
     */
    protected function getErrorLogContext(PaymentTransaction $paymentTransaction): array
    {
        return [
            'payment_transaction_id' => $paymentTransaction->getId(),
            'payment_method'         => $paymentTransaction->getPaymentMethod()
        ];
    }

    /**
     * @param string $message
     * @param array<string,mixed> $errorContext
     */
    protected function logError(string $message, array $errorContext): void
    {
        $this->logger->error($message, $errorContext);
    }

    /**
     * Extracts the payment nonce out of the additional data array
     */
    protected function getNonce(PaymentTransaction $paymentTransaction): string
    {
        $transactionOptions = $paymentTransaction->getTransactionOptions();

        if (!isset($transactionOptions['additionalData'])) {
            throw new \InvalidArgumentException('Payment Transaction does not contain additionalData');
        }

        $additionalData = json_decode($transactionOptions['additionalData'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(
                "Error decoding Payment Transaction additional data Error: " . json_last_error_msg()
            );
        }

        if (!isset($additionalData['nonce'])) {
            throw new \InvalidArgumentException('Payment Transaction additionalData does not contain a nonce');
        }

        return $additionalData['nonce'];
    }

    /**
     * Builds up the request data array by firing off 2 events, one generic and one named after the action type
     * @param PaymentTransaction $paymentTransaction
     * @return array<string,mixed>
     */
    protected function buildRequestData(PaymentTransaction $paymentTransaction): array
    {
        $data = [
            'amount' => $paymentTransaction->getAmount(),
            'paymentMethodNonce' => $this->getNonce($paymentTransaction),
        ];
        
        $event = new BraintreePaymentActionEvent($this->getName(), $data, $paymentTransaction, $this->config);
        $this->eventDispatcher->dispatch($event);

        return $event->getData();
    }
}
