<?php

namespace Oro\Bundle\PayPalBundle\Tests\Unit\EventListener\Callback;

use Entrepids\Bundle\BraintreeBundle\EventListener\Callback\BraintreeCheckoutListener;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Event\CallbackErrorEvent;
use Oro\Bundle\PaymentBundle\Event\CallbackReturnEvent;
use Oro\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Oro\Bundle\PayPalBundle\EventListener\Callback\PayflowExpressCheckoutListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class PayflowExpressCheckoutListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var PayflowExpressCheckoutListener */
    protected $listener;

    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $dispatcher */
    protected $logger;

    /** @var PaymentMethodProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentMethodProvider;

    protected function setUp()
    {
        $this->paymentMethodProvider = $this->createMock(PaymentMethodProviderInterface::class);
        $this->logger = $this->createMock('Psr\Log\LoggerInterface');

        $this->listener = new BraintreeCheckoutListener($this->paymentMethodProvider);
        $this->listener->setLogger($this->logger);
    }

    protected function tearDown()
    {
        unset($this->listener, $this->logger);
    }

    public function testOnError()
    {
        $this->paymentMethodProvider
            ->expects(static::once())
            ->method('hasPaymentMethod')
            ->with('payment_method')
            ->willReturn(true);

        $transaction = new PaymentTransaction();
        $transaction
            ->setSuccessful(true)
            ->setActive(true)
            ->setPaymentMethod('payment_method');

        $event = new CallbackErrorEvent([]);
        $event->setPaymentTransaction($transaction);

        $this->listener->onError($event);

        $this->assertFalse($transaction->isActive());
        $this->assertFalse($transaction->isSuccessful());
    }

    public function testOnErrorWithoutPaymentTransaction()
    {
        $event = new CallbackErrorEvent([]);

        $this->listener->onError($event);
    }

    public function testOnErrorWithWrongTransaction()
    {
        $this->paymentMethodProvider
            ->expects(static::once())
            ->method('hasPaymentMethod')
            ->with('payment_method')
            ->willReturn(false);

        $transaction = $this->createMock(PaymentTransaction::class);
        $transaction
            ->expects($this->once())
            ->method('getPaymentMethod')
            ->willReturn('payment_method');

        $transaction
            ->expects($this->never())
            ->method('setSuccessful');

        $transaction
            ->expects($this->never())
            ->method('setActive');

        $event = new CallbackErrorEvent([]);
        $event->setPaymentTransaction($transaction);

        $this->listener->onError($event);
    }
}
