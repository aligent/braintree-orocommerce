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
		
		$purchaseAction = $this->config->getPurchaseAction();
		
		
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
	public function capture(PaymentTransaction $paymentTransaction)
	{
		$options = $this->getPaymentOptions($paymentTransaction);
		$paymentTransaction->setRequest($options);
		//Aca tengo que obtener el transactionID y realizar la llamada a Braintree mediante el adapter
		$sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
		if (!$sourcePaymentTransaction) {
			$paymentTransaction
			->setSuccessful(false)
			->setActive(false);
		
			return ['successful' => false];
		}
		
		if ($sourcePaymentTransaction->isClone()) {
			return $this->charge($paymentTransaction);
		}
		
		unset($options[Option\Currency::CURRENCY]);
		
		
		$paymentTransaction
		->setRequest($options)
		->setSuccessful($response->isSuccessful())
		->setActive(false)
		->setReference($response->getReference())
		->setResponse($response->getData());
		
		$sourcePaymentTransaction->setActive(!$paymentTransaction->isSuccessful());
		
		return [
				'message' => $response->getMessage(),
				'successful' => $response->isSuccessful(),
		];		
	}
	/**
	 * @param PaymentTransaction $paymentTransaction
	 * @return array
	 */
	public function purchase(PaymentTransaction $paymentTransaction)
	{
		// Aca cambiar por El Adapter o como lo hace Magento
		//$nonce = $this->adapter->createNonce('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		//$this->adapter->find('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		$sourcepaymenttransaction = $paymentTransaction->getSourcePaymentTransaction();
		if ($sourcepaymenttransaction != null){
			$transactionOptions = $sourcepaymenttransaction->getTransactionOptions();
			$nonce = $transactionOptions['nonce'];
			$responseTransaction = $paymentTransaction->getResponse();
			$request = (array)$paymentTransaction->getRequest();
			
			$purchaseAction = $this->config->getPurchaseAction();
			// authorize or charge
			// si charge mando true
			// si authorize mando false
			$submitForSettlement = true;
			$isAuthorize=false;
			$isCharge=false;
			if (strcmp("authorize", $purchaseAction) == 0){
				$submitForSettlement = false;
				$isAuthorize=true;
			}
			if (strcmp("charge", $purchaseAction) == 0){
				$submitForSettlement = true;
				$isCharge=true;
			}			
			
			$data = [
					'amount' => $paymentTransaction->getAmount(),
					'paymentMethodNonce' => $nonce,
					'options' => [
							'submitForSettlement' => $submitForSettlement
					]
			];
			$response = $this->adapter->sale($data);
			
			if ($response->success || !is_null($response->transaction)) {
				// Esto es si chage
				$transaction = $response->transaction;
				
				if ($isCharge){
					$paymentTransaction
					->setAction(self::PURCHASE)
					->setActive(false)
					->setSuccessful($response->success);
				}
				
				//Esto es si authorizr
				if ($isAuthorize){
					$transactionID = $transaction->id;
					$paymentTransaction
					->setAction(self::AUTHORIZE)
					->setActive(true)
					->setSuccessful($response->success);
					
					$transactionOptions = $paymentTransaction->getTransactionOptions();
					$transactionOptions['transactionId'] = $transactionID;
					$paymentTransaction->setTransactionOptions($transactionOptions);
					
				}
				
				$sourcepaymenttransaction
				->setActive(false);

			} else {
				$errorString = "";
			
				foreach($response->errors->deepAll() as $error) {
					$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
				}
				$paymentTransaction
				->setAction(self::VALIDATE)
				->setActive(false)
				->setSuccessful(false);
			
			}
		}
		

		


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

/*        $options = array_merge(
            $this->getPaymentOptions($paymentTransaction),
        	[]
            //$this->getSecureTokenOptions($paymentTransaction)
        );

        $paymentTransaction
            ->setRequest($options)
            ->setAction(PaymentMethodInterface::VALIDATE);

        $this->authorize($paymentTransaction);*/

        $nonce = $_POST["payment_method_nonce"];
        $transactionOptions = $paymentTransaction->getTransactionOptions();
        $transactionOptions['nonce'] = $nonce;
        $paymentTransaction->setTransactionOptions($transactionOptions);
        
        $paymentTransaction
        ->setSuccessful(true)
        ->setAction(self::VALIDATE)
        ->setActive(true);
        
        return [];
        //return $this->secureTokenResponse($paymentTransaction);
	}

	/**
	 * @param PaymentTransaction $paymentTransaction
	 */
	public function complete(PaymentTransaction $paymentTransaction)
	{
		if ($paymentTransaction->getAction() === PaymentMethodInterface::CHARGE) {
			$paymentTransaction->setActive(false);
		}
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
		//$this->adapter->find('sandbox_xbhxzdjx_n2w2d522qmdbjjv9');
		$nonce = $_POST["payment_method_nonce"];
		$transactionOptions = $paymentTransaction->getTransactionOptions();
		$transactionOptions['nonce'] = $nonce;
		$paymentTransaction->setTransactionOptions($transactionOptions);
		/*$nonce = $_POST["payment_method_nonce"];
		$responseTransaction = $paymentTransaction->getResponse();
		$request = (array)$paymentTransaction->getRequest();
		$data = [ 
				'amount' => 145,
				'paymentMethodNonce' => $nonce,
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
*/
		$paymentTransaction
		->setSuccessful(true)
		->setAction(self::VALIDATE)
		->setActive(true);
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