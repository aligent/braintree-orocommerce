<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Provider;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;
use Entrepids\Bundle\BraintreeBundle\Method\Config\Provider\BraintreeConfigProviderInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Factory\BraintreePaymentMethodFactory;
use Oro\Bundle\PaymentBundle\Method\Provider\AbstractPaymentMethodProvider;

class BraintreeMethodProvider extends AbstractPaymentMethodProvider
{
    /**
     * @internal
     */
    public const NEWCREDITCARD = 'newCreditCard';
    /**
     * @var BraintreePaymentMethodFactory
     */
    protected $factory;

    /**
     * @var BraintreeConfigProviderInterface
     */
    private $configProvider;

    /**
     * @param BraintreeConfigProviderInterface $configProvider
     * @param BraintreePaymentMethodFactory $factory
     */
    public function __construct(
        BraintreeConfigProviderInterface $configProvider,
        BraintreePaymentMethodFactory $factory
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
     * @param BraintreeConfig $config
     */
    protected function addBraintreeMethod(BraintreeConfig $config)
    {
        $this->addMethod(
            $config->getPaymentMethodIdentifier(),
            $this->factory->create($config)
        );
    }
}
