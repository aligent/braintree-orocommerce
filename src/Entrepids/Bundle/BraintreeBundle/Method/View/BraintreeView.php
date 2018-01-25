<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\View;

use Entrepids\Bundle\BraintreeBundle\Method\EntrepidsBraintreeMethod;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Provider\PaymentTransactionProvider;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Entrepids\Bundle\BraintreeBundle\Form\Type\CreditCardType;
use Symfony\Component\Form\FormFactoryInterface;

class BraintreeView implements PaymentMethodViewInterface
{

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var BraintreeConfigInterface
     */
    protected $config;

    /**
     * @var PaymentTransactionProvider
     */
    protected $paymentTransactionProvider;

    /**
     *
     * @param FormFactoryInterface $formFactory
     * @param BraintreeConfigInterface $config
     * @param PaymentTransactionProvider $paymentTransactionProvider
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        BraintreeConfigInterface $config,
        PaymentTransactionProvider $paymentTransactionProvider
    ) {
        $this->formFactory = $formFactory;
        $this->config = $config;
        $this->paymentTransactionProvider = $paymentTransactionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(PaymentContextInterface $context)
    {
        $formOptions = [
            'zeroAmountAuthorizationEnabled' => $this->config->isEnableSaveForLater()
        ];
        
        // ORO REVIEW:
        // Why BraintreeConfigInterface is used as data for CreditCardType form type?
        // Waiting
        $config = $this->config;
        $formView = $this->formFactory->create(CreditCardType::NAME, $config, $formOptions)->createView();
        
        $viewOptions = [
            'formView' => $formView,
            'creditCardComponentOptions' => [
                'allowedCreditCards' => $this->getAllowedCreditCards()
            ]
        ];
        
        $validateTransaction = $this->paymentTransactionProvider->
            getActiveValidatePaymentTransaction(
                $this->getPaymentMethodType()
            );
        
        if (! $validateTransaction) {
            return $viewOptions;
        }
        
        $transactionOptions = $validateTransaction->getTransactionOptions();
        
        $viewOptions['creditCardComponent'] = 'braintree/js/app/components/authorized-credit-card-component';
        
        $viewOptions['creditCardComponentOptions'] = array_merge($viewOptions['creditCardComponentOptions'], [
            'saveForLaterUse' => ! empty($transactionOptions['saveForLaterUse'])
        ]);
        
        return $viewOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlock()
    {
        return '_payment_methods_braintree_widget';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->config->getOrder();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->config->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getShortLabel()
    {
        return $this->config->getShortLabel();
    }

    public function getPaymentMethodType()
    {
        return EntrepidsBraintreeMethod::TYPE;
    }

    /**
     * (non-PHPdoc)
     * @see \Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface::getAdminLabel()
     */
    public function getAdminLabel()
    {
        return $this->config->getAdminLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethodIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    /**
     *
     * @return array
     */
    private function getAllowedCreditCards()
    {
        return $this->config->getAllowedCreditCards();
    }
}
