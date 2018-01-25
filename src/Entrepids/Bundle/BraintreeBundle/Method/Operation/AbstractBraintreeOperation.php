<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Operation;

use Braintree\Exception\NotFound;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Interfaces\OperationInterface;

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
     * @var BraintreeConfigInterface
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
     * @param BraintreeConfigInterface $config
     */
    public function __construct(
        Session $session,
        TranslatorInterface $translator,
        PropertyAccessor $propertyAccessor,
        DoctrineHelper $doctrineHelper,
        BraintreeAdapter $braintreeAdapter,
        BraintreeConfigInterface $config
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->adapter = $braintreeAdapter;
        $this->config = $config;
        $this->propertyAccessor = $propertyAccessor;
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * (non-PHPdoc)
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\Interfaces\OperationInterface::operationProcess()
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
