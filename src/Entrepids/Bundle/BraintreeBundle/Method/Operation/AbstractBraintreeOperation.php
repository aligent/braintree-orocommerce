<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\OperationInterface;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractBraintreeOperation implements OperationInterface
{

    /**
     *
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

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

    /**
     * @var Session
     */
    protected $session;

    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     *
     * @param Session $session
     * @param TranslatorInterface $translator
     * @param PropertyAccessor $propertyAccessor
     * @param DoctrineHelper $doctrineHelper
     * @param BraintreeAdapter $braintreeAdapter
     */
    public function __construct(
        Session $session,
        TranslatorInterface $translator,
        PropertyAccessor $propertyAccessor,
        DoctrineHelper $doctrineHelper
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->propertyAccessor = $propertyAccessor;
        $this->session = $session;
        $this->translator = $translator;
    }


    public function setConfig(BraintreeConfig $config) {
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
    abstract protected function preProcessOperation();

    /**
     * This method is used to postprecess the information of the operation
     */
    abstract protected function postProcessOperation();

    /**
     * This method is used when exists data to send to braintree core
     */
    abstract protected function preprocessDataToSend();

    /**
     *
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        return $this->propertyAccessor;
    }
}
