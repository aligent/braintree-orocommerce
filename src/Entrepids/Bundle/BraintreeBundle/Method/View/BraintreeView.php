<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\View;

use Entrepids\Bundle\BraintreeBundle\Method\Braintree;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Provider\PaymentTransactionProvider;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Entrepids\Bundle\BraintreeBundle\Form\Type\CreditCardType;
use Symfony\Component\Form\FormFactoryInterface;

class BraintreeView implements PaymentMethodViewInterface
{
	/** @var FormFactoryInterface */
	protected $formFactory;
	/** @var BraintreeConfigInterface */
	protected $config;
	/** @var PaymentTransactionProvider */
	protected $paymentTransactionProvider;
	
	/**
	 * @param FormFactoryInterface $formFactory
	 * @param BraintreeConfigInterface $config
	 * @param PaymentTransactionProvider $paymentTransactionProvider
	 */
	public function __construct(FormFactoryInterface $formFactory, BraintreeConfigInterface $config, PaymentTransactionProvider $paymentTransactionProvider)
	{
		$this->formFactory = $formFactory;
		$this->config = $config;
		$this->paymentTransactionProvider = $paymentTransactionProvider;
	}

	/** {@inheritdoc} */
	public function getOptions(PaymentContextInterface $context)
	{

        $formOptions = [
            'zeroAmountAuthorizationEnabled' => $this->config->isEnableSaveForLater(),
            //'requireCvvEntryEnabled' => $this->config->isEnabledCvvVerification(),
        ];

        $config = $this->config;
        $formView = $this->formFactory->create(CreditCardType::NAME, $config, $formOptions)->createView();

        $viewOptions = [
            'formView' => $formView,
            'creditCardComponentOptions' => [
                'allowedCreditCards' => $this->getAllowedCreditCards(),
            ],
        ];

        $validateTransaction = $this->paymentTransactionProvider
            ->getActiveValidatePaymentTransaction($this->getPaymentMethodType());

        if (!$validateTransaction) {
            return $viewOptions;
        }

        $transactionOptions = $validateTransaction->getTransactionOptions();

        $viewOptions['creditCardComponent'] = 'braintree/js/app/components/authorized-credit-card-component';

        $viewOptions['creditCardComponentOptions'] = array_merge($viewOptions['creditCardComponentOptions'], [
            //'acct' => $this->getLast4($validateTransaction),
            'saveForLaterUse' => !empty($transactionOptions['saveForLaterUse']),
        ]);

        return $viewOptions;
    }
    
	/** {@inheritdoc} */
	public function getBlock()
	{
		return '_payment_methods_braintree_widget';
	}

	/** {@inheritdoc} */
	public function getOrder()
	{
		return $this->config->getOrder();
	}

	/** {@inheritdoc} */
	public function getLabel()
	{
		return $this->config->getLabel();
	}

	/** {@inheritdoc} */
	public function getShortLabel()
	{
		return $this->config->getShortLabel();
	}

	public function getPaymentMethodType()
	{
		return Braintree::TYPE;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getAdminLabel()
	{
		return $this->config->getAdminLabel();
	}
	
	/** {@inheritdoc} */
	public function getPaymentMethodIdentifier()
	{
		return $this->config->getPaymentMethodIdentifier();
	}

	/**
	 * @return array
	 */
	public function getAllowedCreditCards()
	{
		return $this->config->getAllowedCreditCards();
	}

	/**
	 * @return array
	 */
	public function getAllowedEnvironmentTypes()
	{
		return $this->config->getAllowedEnvironmentTypes();
	}	
	/**
	 * @return string
	 */
	public function getSandBoxMerchId(){
		return $this->config->getSandBoxMerchId();
	}
	/**
	 * @return string
	*/
	public function getSandBoxMerchAccountId(){
		return $this->config->getSandBoxMerchAccountId();
	}
	/**
	 * @return string
	*/
	public function getSandBoxPublickKey(){
		return $this->config->getSandBoxPublickKey();
	}
	/**
	 * @return string
	*/
	public function getSandBoxPrivateKey(){
		return $this->config->getSandBoxPrivateKey();
	}
	/**
	 * @return bool
	 */
	public function isCreditCardEnabled(){
		return $this->config->isCreditCardEnabled();
	}
	
	/**
	 * @return string
	 */
	public function getSandBoxCreditCardTitle(){
		return $this->config->getSandBoxCreditCardTitle();
	}
	
	/**
	 * @return string
	 */
	public function getPurchaseAction(){
		return $this->config->getPurchaseAction();
	}
	/**
	 * @return bool
	 */
	public function isEnabledVaultSavedCards(){
		return $this->config->isEnabledVaultSavedCards();
	}
	/**
	 * @return bool
	 */
	public function isDisplayCreditCard(){
		return $this->config->isDisplayCreditCard();
	}	
}
