<?php

namespace Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\Method;

use Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\Method\Config\CollectOnDeliveryConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Psr\Log\LoggerInterface;

class CollectOnDelivery implements PaymentMethodInterface
{
    const TYPE = 'collect_on_delivery';

    /** @var CollectOnDeliveryConfigInterface */
    private $config;
    
    private $logger;

    /**
     * @param CollectOnDeliveryConfigInterface $config
     */
    public function __construct(CollectOnDeliveryConfigInterface $config, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /** {@inheritdoc} */
    public function execute($action, PaymentTransaction $paymentTransaction)
    {
    	 if (null !== $this->logger) {
		    	$this->logger->info('CollectionOnDelivery execute '.$action);
    	 }
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
