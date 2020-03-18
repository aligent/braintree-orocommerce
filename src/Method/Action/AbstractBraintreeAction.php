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
use \InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractBraintreeAction implements BraintreeActionInterface, LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * @var Gateway
     */
    protected $braintreeGateway;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var BraintreeConfigInterface
     */
    protected $config;

    /**
     * AbstractBraintreeAction constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, DoctrineHelper $doctrineHelper)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param BraintreeConfigInterface $braintreeConfig
     * @return void
     */
    public function initialize(BraintreeConfigInterface $braintreeConfig)
    {
        $this->config = $braintreeConfig;
        $this->braintreeGateway = new Gateway($braintreeConfig, $this->doctrineHelper);
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     */
    protected function setPaymentTransactionStateFailed(PaymentTransaction $paymentTransaction)
    {
        $paymentTransaction
            ->setSuccessful(false)
            ->setActive(false);
    }

    /**
     * Logs error
     *
     * @param PaymentTransaction $paymentTransaction
     * @param Error $error
     */
    protected function handleError(PaymentTransaction $paymentTransaction, Error $error)
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
     *
     * @param PaymentTransaction $paymentTransaction
     * @param \Throwable         $exceptionOrError
     */
    protected function handleException(PaymentTransaction $paymentTransaction, \Throwable $exceptionOrError)
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
     * @return array
     */
    protected function getErrorLogContext(PaymentTransaction $paymentTransaction)
    {
        $result = [
            'payment_transaction_id' => $paymentTransaction->getId(),
            'payment_method'         => $paymentTransaction->getPaymentMethod()
        ];

        return $result;
    }

    /**
     * @param string $message
     * @param array  $errorContext
     */
    protected function logError($message, array $errorContext)
    {
        $this->logger->error($message, $errorContext);
    }

    /**
     * Extracts the payment nonce out of the additional data array
     * @param PaymentTransaction $paymentTransaction
     * @return string
     */
    protected function getNonce(PaymentTransaction $paymentTransaction)
    {
        $transactionOptions = $paymentTransaction->getTransactionOptions();

        if (!isset($transactionOptions['additionalData'])) {
            throw new InvalidArgumentException('Payment Transaction does not contain additionalData');
        }

        $additionalData = json_decode($transactionOptions['additionalData'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                "Error decoding Payment Transaction additional data Error: " . json_last_error_msg()
            );
        }

        if (!isset($additionalData['nonce'])) {
            throw new InvalidArgumentException('Payment Transaction additionalData does not contain a nonce');
        }

        return $additionalData['nonce'];
    }

    /**
     * Builds up the request data array by firing off 2 events, one generic and one named after the action type
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function buildRequestData(PaymentTransaction $paymentTransaction)
    {
        $data = [
            'amount' => $paymentTransaction->getAmount(),
            'paymentMethodNonce' => $this->getNonce($paymentTransaction)
        ];
        
        $event = new BraintreePaymentActionEvent($data, $paymentTransaction, $this->config);

        // Generic Event
        $this->eventDispatcher->dispatch(BraintreePaymentActionEvent::NAME, $event);

        // Action named event
        $this->eventDispatcher->dispatch(
            sprintf(
                BraintreePaymentActionEvent::ACTION_EVENT_NAME,
                $this->getName()
            ),
            $event
        );

        return $event->getData();
    }
}
