<?php
namespace Entrepids\Bundle\BraintreeBundle\EventListener\Callback;

use Entrepids\Bundle\BraintreeBundle\Method\EntrepidsBraintreeMethod;
use Oro\Bundle\PaymentBundle\Event\AbstractCallbackEvent;
use Oro\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * This is the class that check and validate a operation in the checkout
 */
class BraintreeCheckoutListener
{
    use LoggerAwareTrait;

    /**
     *
     * @var PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     *
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     */
    public function __construct(PaymentMethodProviderInterface $paymentMethodProvider)
    {
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     *
     * @param AbstractCallbackEvent $event
     */
    public function onError(AbstractCallbackEvent $event)
    {
        $paymentTransaction = $event->getPaymentTransaction();
        
        if (! $paymentTransaction) {
            return;
        }
        
        $paymentTransaction->setSuccessful(false)->setActive(false);
    }

}