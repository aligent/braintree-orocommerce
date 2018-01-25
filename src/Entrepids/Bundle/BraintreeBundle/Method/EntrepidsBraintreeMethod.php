<?php
namespace Entrepids\Bundle\BraintreeBundle\Method;

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
use Entrepids\Bundle\BraintreeBundle\Helper\BraintreeHelper;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseData;

class EntrepidsBraintreeMethod implements PaymentMethodInterface
{

    const TYPE = 'entrepids_braintree';

    const COMPLETE = 'complete';

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
     * @var PurchaseData
     */
    protected $purchaseData;
    
    /**
     *
     * @var BraintreeConfigInterface
     */
    private $config;

    /**
     *
     * @param BraintreeConfigInterface $config
     * @param DoctrineHelper $doctrineHelper
     * @param PropertyAccessor $propertyAccessor
     * @param Session $session
     * @param TranslatorInterface $translator
     * @param PurchaseData $purchaseData
     */
    public function __construct(
        BraintreeConfigInterface $config,
        DoctrineHelper $doctrineHelper,
        PropertyAccessor $propertyAccessor,
        Session $session,
        TranslatorInterface $translator,
        PurchaseData $purchaseData
    ) {
        $this->config = $config;
        $this->doctrineHelper = $doctrineHelper;
        $this->propertyAccessor = $propertyAccessor;
        $this->session = $session;
        $this->translator = $translator;
        $this->purchaseData = $purchaseData;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($action, PaymentTransaction $paymentTransaction)
    {
        if (! $this->supports($action)) {
            throw new \InvalidArgumentException(sprintf('Unsupported action "%s"', $action));
        }
        
        return $this->{$action}($paymentTransaction) ?  : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(PaymentContextInterface $context)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($actionName)
    {
        if ($actionName === self::VALIDATE) {
            return true;
        }
        
        return in_array((string) $actionName, [
            self::AUTHORIZE,
            self::CAPTURE,
            self::CHARGE,
            self::PURCHASE,
            self::COMPLETE
        ], true);
    }

    /**
     *
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function capture(PaymentTransaction $paymentTransaction)
    {
        $this->executeBraintreeHelper($paymentTransaction, PaymentMethodInterface::CAPTURE);
    }

    /**
     *
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function charge(PaymentTransaction $paymentTransaction)
    {
        $this->executeBraintreeHelper($paymentTransaction, PaymentMethodInterface::CHARGE);
    }

    /**
     *
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function purchase(PaymentTransaction $paymentTransaction)
    {
        $sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction();
        $purchaseOperation = $this->purchaseData->getPurchaseError();
        if ($sourcepaymenttransaction != null) {
            $sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction();
            
            $transactionOptions = $sourcepaymenttransaction->getTransactionOptions();
            $nonce = $transactionOptions['nonce'];
            if (array_key_exists('credit_card_value', $transactionOptions)) {
                $creditCardValue = $transactionOptions['credit_card_value'];
            } else {
                $creditCardValue = $this->purchaseData->getNewCreditCard();
            }
            
            $purchaseNewCreditCard = $this->purchaseData->getNewCreditCard();
            if ((! empty($creditCardValue)) && (strcmp($creditCardValue, $purchaseNewCreditCard) != 0)) {
                $purchaseOperation = $this->purchaseData->getPurchaseExisting();
            } else {
                $purchaseOperation = $this->purchaseData->getPurchaseNewCreditCard();
            }
        }
        
        $this->executeBraintreeHelper($paymentTransaction, PaymentMethodInterface::PURCHASE, $purchaseOperation);
    }

    /**
     *
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function validate(PaymentTransaction $paymentTransaction)
    {
        $this->executeBraintreeHelper($paymentTransaction, PaymentMethodInterface::VALIDATE);
    }

    /**
     *
     * @param PaymentTransaction $paymentTransaction
     */
    protected function complete(PaymentTransaction $paymentTransaction)
    {
        $this->executeBraintreeHelper($paymentTransaction, $this::COMPLETE);
    }

    /**
     *
     * @param PaymentTransaction $paymentTransaction
     */
    protected function authorize(PaymentTransaction $paymentTransaction)
    {
        $this->executeBraintreeHelper($paymentTransaction, PaymentMethodInterface::AUTHORIZE);
    }

    /**
     * {@inheritdoc}
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
     * Create and execute the BraintreeHelper with specific operation
     *
     * @param PaymentTransaction $paymentTransaction
     * @param unknown $paymentMethodOperation
     * @param string $operation
     */
    private function executeBraintreeHelper(
        PaymentTransaction $paymentTransaction,
        $paymentMethodOperation,
        $operation = null
    ) {
        $braintreeHelper = new BraintreeHelper(
            $this->config,
            $this->doctrineHelper,
            $this->propertyAccessor,
            $this->session,
            $this->translator,
            $paymentMethodOperation,
            $this->purchaseData
        );
        
        $braintreeHelper->execute($paymentTransaction, $operation);
    }
}
