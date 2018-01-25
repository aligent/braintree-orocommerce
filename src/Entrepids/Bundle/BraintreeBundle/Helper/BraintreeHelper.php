<?php
namespace Entrepids\Bundle\BraintreeBundle\Helper;

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
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\ExistingCreditCardPurchase;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\NewCreditCardPurchase;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Validate\OperationValidate;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Capture\OperationCapture;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Charge\OperationCharge;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Complete\OperationComplete;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Authorize\OperationAuthorize;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseData;

/**
 * This is the class that execute a determined operation in the step of checkout
 */
class BraintreeHelper implements BraintreeHelperInterface
{

    /**
     *
     * @var BraintreeConfigInterface
     */
    protected $config;

    /**
     *
     * @var BraintreeAdapter
     */
    protected $adapter;

    /**
     *
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     *
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     *
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
     * @var String
     */
    protected $paymentOperation;

    /**
     *
     * @var String
     */
    protected $genericOperation;

    /**
     *
     * @var array
     */
    protected $operationsValue = [];

    /**
     *
     * @var PurchaseData
     */
    protected $purchaseData;

    /**
     *
     * @param BraintreeConfigInterface $config
     * @param DoctrineHelper $doctrineHelper
     * @param PropertyAccessor $propertyAccessor
     * @param Session $session
     * @param TranslatorInterface $translator
     * @param unknown $operation
     * @param PurchaseData $purchaseData
     */
    public function __construct(
        BraintreeConfigInterface $config,
        DoctrineHelper $doctrineHelper,
        PropertyAccessor $propertyAccessor,
        Session $session,
        TranslatorInterface $translator,
        $operation,
        PurchaseData $purchaseData
    ) {
        $this->config = $config;
        $this->adapter = new BraintreeAdapter($this->config);
        $this->adapter->initCredentials();
        $this->doctrineHelper = $doctrineHelper;
        $this->propertyAccessor = $propertyAccessor;
        $this->session = $session;
        $this->translator = $translator;
        $this->paymentOperation = $operation;
        $this->purchaseData = $purchaseData;
        
        $this->operationsValue = array(
            PaymentMethodInterface::CAPTURE =>
                  "Entrepids\Bundle\BraintreeBundle\Method\Operation\Capture\OperationCapture",
            PaymentMethodInterface::CHARGE =>
                   "Entrepids\Bundle\BraintreeBundle\Method\Operation\Charge\OperationCharge",
            $this->purchaseData->getPurchaseExisting() =>
                   "Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\ExistingCreditCardPurchase",
            $this->purchaseData->getPurchaseNewCreditCard() =>
                   "Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\NewCreditCardPurchase",
            $this->purchaseData->getPurchaseError() =>
                   "Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseErrorOperation",
            PaymentMethodInterface::VALIDATE =>
                   "Entrepids\Bundle\BraintreeBundle\Method\Operation\Validate\OperationValidate",
            PaymentMethodInterface::AUTHORIZE =>
                   "Entrepids\Bundle\BraintreeBundle\Method\Operation\Authorize\OperationAuthorize"
        )
        ;
    }

    /**
     * This method execute a operation that is passed by parameter
     *
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Helper\BraintreeHelperInterface::execute()
     */
    public function execute(PaymentTransaction $paymentTransaction, $operation = null)
    {
        $operationParam = $this->paymentOperation;
        if (isset($operation)) {
            $operationParam = $operation;
        }
        $operationExecute = $this->operationsValue[$operationParam];
        try {
            $genericOperation = new $operationExecute(
                $this->session,
                $this->translator,
                $this->propertyAccessor,
                $this->doctrineHelper,
                $this->adapter,
                $this->config
            );
            $this->setGenericOperation($genericOperation);
            $this->setAndExecuteOperation($paymentTransaction);
        } catch (\Exception $e) {
            $paymentTransaction->setAction($this->paymentOperation)
                ->setActive(false)
                ->setSuccessful(false);
            $paymentTransaction->getSourcePaymentTransaction()
                ->setActive(false)
                ->setSuccessful(false);
        }
    }

    /**
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    /**
     *
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        return $this->propertyAccessor;
    }

    /**
     */
    protected function setGenericOperation($genericOperation)
    {
        $this->genericOperation = $genericOperation;
    }

    /**
     *
     * @return String
     */
    protected function getGenericOperation()
    {
        return $this->genericOperation;
    }

    /**
     *
     * @param unknown $paymentTransaction
     */
    protected function setAndExecuteOperation($paymentTransaction)
    {
        return $this->getGenericOperation()->operationProcess($paymentTransaction);
    }
}
