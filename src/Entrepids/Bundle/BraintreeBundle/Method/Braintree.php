<?php

namespace Entrepids\Bundle\BraintreeBundle\Method;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;

class Braintree implements PaymentMethodInterface
{
	const TYPE = 'braintree';

	/** @var BraintreeConfigInterface */
	private $config;
	
	/** @var RouterInterface */
	protected $router;
	
	/**
	 * @param BraintreeConfigInterface $config
	 * @param RouterInterface $router
	 */
	public function __construct(BraintreeConfigInterface $config, RouterInterface $router)
	{
		$this->config = $config;
		$this->router = $router;
	}

	/** {@inheritdoc} */
	public function execute($action, PaymentTransaction $paymentTransaction)
	{
		switch ($action) {
			case self::PURCHASE:
				$paymentTransaction
				->setAction(self::AUTHORIZE)
				->setActive(true)
				->setSuccessful(true);
				break;
			case self::CAPTURE:
				$paymentTransaction
				->setActive(false)
				->setSuccessful(true);

				$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
				if ($sourcePaymentTransaction) {
					$sourcePaymentTransaction->setActive(false);
				}
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Action %s not supported', $action));
		}

		return [];
	}

	/** {@inheritdoc} */
	public function getType()
	{
		return self::TYPE;
	}

	/** {@inheritdoc} */
	public function isEnabled()
	{
		return $this->config->isEnabled();
	}

	/** {@inheritdoc} */
	public function isApplicable(PaymentContextInterface $context)
	{
		/*return $this->config->isCountryApplicable($context)
		 && $this->config->isCurrencyApplicable($context);*/
		return true;
	}

	/** {@inheritdoc} */
	public function supports($actionName)
	{
		return in_array((string)$actionName, [self::PURCHASE, self::CAPTURE], true);
	}
}