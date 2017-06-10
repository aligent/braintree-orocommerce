<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Provider;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Config\Provider\BraintreeConfigProviderInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Factory\BraintreePaymentMethodFactoryInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\AbstractPaymentMethodProvider;

class BraintreeMethodProvider extends AbstractPaymentMethodProvider
{
    /**
     * @var BraintreePaymentMethodFactoryInterface
     */
    protected $factory;

    /**
     * @var BraintreeConfigProviderInterface
     */
    private $configProvider;

    /**
     * @param BraintreeConfigProviderInterface $configProvider
     * @param BraintreePaymentMethodFactoryInterface $factory
     */
    public function __construct(
        BraintreeConfigProviderInterface $configProvider,
        BraintreePaymentMethodFactoryInterface $factory
    ) {
        parent::__construct();

        $this->configProvider = $configProvider;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    protected function collectMethods()
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->addBraintreeMethod($config);
        }
    }

    /**
     * @param BraintreeConfigInterface $config
     */
    protected function addBraintreeMethod(BraintreeConfigInterface $config)
    {
        $this->addMethod(
            $config->getPaymentMethodIdentifier(),
            $this->factory->create($config)
        );
    }
}
