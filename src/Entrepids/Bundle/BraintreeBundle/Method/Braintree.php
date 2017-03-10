<?php

namespace Entrepids\Bundle\BraintreeBundle\Method;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class Braintree implements PaymentMethodInterface
{
	const TYPE = 'braintree';

	/** @var BraintreeConfigInterface */
	private $config;

	/**
	 * @param BraintreeConfigInterface $config
	 */
	public function __construct(BraintreeConfigInterface $config)
	{
		$this->config = $config;
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