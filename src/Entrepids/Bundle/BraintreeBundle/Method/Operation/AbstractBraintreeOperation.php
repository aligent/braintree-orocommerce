<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

abstract class AbstractBraintreeOperation implements OperationInterface
{

    /**
     *
     * @var BraintreeAdapter
     */
    protected $adapter;

    /**
     *
     * @var PaymentTransaction
     */
    protected $paymentTransaction;

    /**
     *
     * @var BraintreeConfig
     */
    protected $config;


    public function setConfig(BraintreeConfig $config)
    {
        $this->config = $config;

        $this->adapter = new BraintreeAdapter($this->config);
        $this->adapter->initCredentials();

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\OperationInterface::operationProcess()
     */
    public function operationProcess(PaymentTransaction $paymentTransaction)
    {
        $this->paymentTransaction = $paymentTransaction;
        $this->preprocessDataToSend();
        $this->preProcessOperation();
        return $this->postProcessOperation();
    }

    /**
     * This method is used to preprocess the information of the operation
     */
    protected function preProcessOperation()
    {
    }

    /**
     * This method is used to postprecess the information of the operation
     */
    protected function postProcessOperation()
    {
    }

    /**
     * This method is used when exists data to send to braintree core
     */
    protected function preprocessDataToSend()
    {
    }
}
