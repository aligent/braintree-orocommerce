<?php
/**
 *
 *
 * @category  Aligent
 * @package
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2019 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Event;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Component\EventDispatcher\Event;

class BraintreePaymentActionEvent extends Event
{
    const NAME = 'aligent_braintree.payment_action';
    const ACTION_EVENT_NAME = 'aligent_braintree.payment_action.%s';

    /**
     * @var array
     */
    protected $data;

    /**
     * @var PaymentTransaction
     */
    protected $paymentTransaction;

    /**
     * @var BraintreeConfigInterface
     */
    protected $config;

    /**
     * BraintreePaymentActionEvent constructor.
     * @param array $data
     * @param PaymentTransaction $paymentTransaction
     * @param BraintreeConfigInterface $config
     */
    public function __construct(array $data, PaymentTransaction $paymentTransaction, BraintreeConfigInterface $config)
    {
        $this->data = $data;
        $this->paymentTransaction = $paymentTransaction;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return BraintreeConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return PaymentTransaction
     */
    public function getPaymentTransaction()
    {
        return $this->paymentTransaction;
    }
}