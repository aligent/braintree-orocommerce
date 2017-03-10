<?php

namespace Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\Method\View;

use Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\Method\CollectOnDelivery;
use Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\Method\Config\CollectOnDeliveryConfigInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class CollectOnDeliveryView implements PaymentMethodViewInterface
{
    /** @var CollectOnDeliveryConfigInterface */
    private $config;

    /**
     * @param CollectOnDeliveryConfigInterface $config
     */
    public function __construct(CollectOnDeliveryConfigInterface $config)
    {
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function getOptions(PaymentContextInterface $context)
    {
        return [];
    }

    /** {@inheritdoc} */
    public function getBlock()
    {
        return '_payment_methods_collect_on_delivery_widget';
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

    /** {@inheritdoc} */
    public function getPaymentMethodType()
    {
        return CollectOnDelivery::TYPE;
    }
    
    /**
     * @return array
     */
    public function getAllowedCreditCards()
    {
    	return $this->config->getAllowedCreditCards();
    }    
}
