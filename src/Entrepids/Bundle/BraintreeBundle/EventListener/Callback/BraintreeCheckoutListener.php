<?php

namespace Entrepids\Bundle\BraintreeBundle\EventListener\Callback;

use Psr\Log\LoggerAwareTrait;

use Entrepids\Bundle\BraintreeBundle\Method\Braintree;
use Oro\Bundle\PaymentBundle\Event\AbstractCallbackEvent;
use Oro\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;

class BraintreeCheckoutListener {
	use LoggerAwareTrait;
	
    /**
     * @var PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;
	
    /**
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     */
    public function __construct(PaymentMethodProviderInterface $paymentMethodProvider)
    {
        $this->paymentMethodProvider = $paymentMethodProvider;
    }
	
	/**
	 * @param AbstractCallbackEvent $event
	 */
	public function onError(AbstractCallbackEvent $event)
	{
		$paymentTransaction = $event->getPaymentTransaction();
	
		if (!$paymentTransaction) {
			return;
		}
	
		$paymentTransaction
		->setSuccessful(false)
		->setActive(false);
	}
	
	/**
	 * @param AbstractCallbackEvent $event
	 */
	public function onReturn(AbstractCallbackEvent $event)
	{
		$paymentTransaction = $event->getPaymentTransaction();
		
		if (!$paymentTransaction) {
			return;
		}
		
		$paymentMethodId = $paymentTransaction->getPaymentMethod();
		
		if (false === $this->paymentMethodProvider->hasPaymentMethod($paymentMethodId)) {
			return;
		}
		
		$eventData = $event->getData();
		
		if (!$paymentTransaction || !isset($eventData['PayerID'], $eventData['token']) ||
				$eventData['token'] !== $paymentTransaction->getReference()
		) {
			return;
		}
		
		$responseDataFilledWithEventData = array_replace($paymentTransaction->getResponse(), $eventData);
		$paymentTransaction->setResponse($responseDataFilledWithEventData);
		
		try {
			$paymentMethod = $this->paymentMethodProvider->getPaymentMethod($paymentMethodId);
			$paymentMethod->execute(Braintree::COMPLETE, $paymentTransaction);
		
			$event->markSuccessful();
		} catch (\InvalidArgumentException $e) {
			if ($this->logger) {
				// do not expose sensitive data in context
				$this->logger->error($e->getMessage(), []);
			}
		}
		
	}	
}