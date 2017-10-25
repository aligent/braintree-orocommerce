<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\View\Provider;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Config\Provider\BraintreeConfigProviderInterface;
use Entrepids\Bundle\BraintreeBundle\Method\View\Factory\BraintreePaymentMethodViewFactoryInterface;
use Oro\Bundle\PaymentBundle\Method\View\AbstractPaymentMethodViewProvider;

class BraintreeMethodViewProvider extends AbstractPaymentMethodViewProvider
{
    /** @var BraintreePaymentMethodViewFactoryInterface */
    private $factory;

    /** @var BraintreeConfigProviderInterface */
    private $configProvider;

    /**
     * @param BraintreeConfigProviderInterface $configProvider
     * @param BraintreePaymentMethodViewFactoryInterface $factory
     */
    public function __construct(
        BraintreeConfigProviderInterface $configProvider,
        BraintreePaymentMethodViewFactoryInterface $factory
    ) {
        $this->factory = $factory;
        $this->configProvider = $configProvider;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildViews()
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->addBraintreeView($config);
        }
    }

    /**
     * @param BraintreeConfigInterface $config
     */
    protected function addBraintreeView(BraintreeConfigInterface $config)
    {
        $this->addView(
            $config->getPaymentMethodIdentifier(),
            $this->factory->create($config)
        );
    }
}
