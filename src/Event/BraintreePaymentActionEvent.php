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

class BraintreePaymentActionEvent
{
    const NAME = 'aligent_braintree.payment_action';

    protected string $action;
    /** @var array<string,mixed> */
    protected array $data;
    protected PaymentTransaction $paymentTransaction;
    protected BraintreeConfigInterface $config;

    /**
     * @param string $action
     * @param array<string,mixed> $data
     * @param PaymentTransaction $paymentTransaction
     * @param BraintreeConfigInterface $config
     */
    public function __construct(
        string $action,
        array $data,
        PaymentTransaction $paymentTransaction,
        BraintreeConfigInterface $config
    ) {
        $this->action = $action;
        $this->data = $data;
        $this->paymentTransaction = $paymentTransaction;
        $this->config = $config;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return array<string,mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string,mixed> $data
     * @return void
     */
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
