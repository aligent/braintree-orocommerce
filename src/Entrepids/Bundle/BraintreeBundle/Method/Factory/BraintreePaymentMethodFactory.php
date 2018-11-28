<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Factory;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;
use Entrepids\Bundle\BraintreeBundle\Method\EntrepidsBraintreeMethod;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Factory;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseData;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Provider\ExtractOptionsProvider;
use Oro\Bundle\PaymentBundle\Provider\SurchargeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BraintreePaymentMethodFactory
{

    /** @var Factory */
    protected $opFactory;

    /** @var LoggerInterface */
    protected $logger;


    public function __construct(Factory $opFactory, LoggerInterface $logger)
    {
        $this->opFactory = $opFactory;
        $this->logger = $logger;
    }


    /**
     * This method is called when the Braintree method is selected in the checkout process
     *
     * {@inheritdoc}
     */
    public function create(BraintreeConfig $config)
    {
        $method = new EntrepidsBraintreeMethod($this->opFactory, $config);
        $method->setLogger($this->logger);

        return $method;
    }
}
