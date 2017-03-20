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
	
	const COMPLETE = 'complete';
	
	const ZERO_AMOUNT = 0;

	/** @var BraintreeConfigInterface */
	private $config;
	
	/** @var RouterInterface */
	protected $router;
	
	/**
	 * @var BraintreeAdapter
	 */
	protected $adapter;	
	
	/**
	 * @param BraintreeConfigInterface $config
	 * @param RouterInterface $router
	 * @param BraintreeAdapter $adapter
	 */
	public function __construct(BraintreeConfigInterface $config, RouterInterface $router, BraintreeAdapter $adapter)
	{
		$this->config = $config;
		$this->router = $router;
		$this->adapter = $adapter;
	}

	/** {@inheritdoc} */
	public function execute($action, PaymentTransaction $paymentTransaction)
	{
		if (!$this->supports($action)) {
			throw new \InvalidArgumentException(sprintf('Unsupported action "%s"', $action));
		}
		
/*		switch ($action) {
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
		}*/

		return $this->{$action}($paymentTransaction) ?: [];
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
		if ($actionName === self::VALIDATE) {
			return true;
		}
		
		return in_array((string)$actionName, [self::AUTHORIZE, self::CAPTURE, self::CHARGE, self::PURCHASE, self::COMPLETE], true);
	}
	
	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function validate(PaymentTransaction $paymentTransaction)
	{
        $paymentTransaction
            ->setAmount(self::ZERO_AMOUNT)
            ->setCurrency('USD');

        $options = array_merge(
            $this->getPaymentOptions($paymentTransaction),
        	[]
            //$this->getSecureTokenOptions($paymentTransaction)
        );

        $paymentTransaction
            ->setRequest($options)
            ->setAction(PaymentMethodInterface::VALIDATE);

        $this->authorize($paymentTransaction);

        return $this->secureTokenResponse($paymentTransaction);
	}

	/**
	 * @param PaymentTransaction $paymentTransaction
	 */
	public function authorize(PaymentTransaction $paymentTransaction)
	{
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
		if ($sourcePaymentTransaction) {
			$this->useValidateTransactionData($paymentTransaction, $sourcePaymentTransaction);
	
			return;
		}
	
		// Aca cambiar por El Adapter o como lo hace Magento
		//$nonce = $this->adapter->createNonce('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		$this->adapter->find('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		$data = [ 
				'amount' => 145,
				'paymentMethodNonce' => 'nonce_from_the_client',
				'options' => [ 
						'submitForSettlement' => true 
				] 
		];
		$response = $this->adapter->sale($data);
		
		if ($response->success || !is_null($response->transaction)) {
			$transaction = $response->transaction;
		} else {
			$errorString = "";
		
			foreach($response->errors->deepAll() as $error) {
				$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
			}
		
		}

		$paymentTransaction
		->setSuccessful($response->success)
		->setActive($response->success);
		//->setReference($response->getReference())
		//->setResponse($response->getData());
	}
	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	protected function getPaymentOptions(PaymentTransaction $paymentTransaction)
	{
		$options = [
				'AMT' => round($paymentTransaction->getAmount(), 2),
				'TENDER' => 'C',
				'CURRENCY' => $paymentTransaction->getCurrency(),
		];
	
		if ($paymentTransaction->getSourcePaymentTransaction()) {
			$options['ORIGID'] =
			$paymentTransaction->getSourcePaymentTransaction()->getReference();
		}
	
		return $options;
	}	
	
	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @param PaymentTransaction $sourcePaymentTransaction
	 */
	protected function useValidateTransactionData(
			PaymentTransaction $paymentTransaction,
			PaymentTransaction $sourcePaymentTransaction
	) {
		$paymentTransaction
		->setCurrency($sourcePaymentTransaction->getCurrency())
		->setReference($sourcePaymentTransaction->getReference())
		->setSuccessful($sourcePaymentTransaction->isSuccessful())
		->setActive($sourcePaymentTransaction->isActive())
		->setRequest()
		->setResponse();
	}
}