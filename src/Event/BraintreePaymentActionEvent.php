<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Event;

use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Contracts\EventDispatcher\Event;

class BraintreePaymentActionEvent extends Event
{
    const NAME = 'aligent_braintree.payment_action';
    const ACTION_EVENT_NAME = 'aligent_braintree.payment_action.%s';

    protected array $data;

    protected PaymentTransaction $paymentTransaction;

    protected BraintreeConfigInterface $config;

    /**
     * BraintreePaymentActionEvent constructor.
     */
    public function __construct(array $data, PaymentTransaction $paymentTransaction, BraintreeConfigInterface $config)
    {
        $this->data = $data;
        $this->paymentTransaction = $paymentTransaction;
        $this->config = $config;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getConfig(): BraintreeConfigInterface
    {
        return $this->config;
    }

    public function getPaymentTransaction(): PaymentTransaction
    {
        return $this->paymentTransaction;
    }
}
