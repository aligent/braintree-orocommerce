<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/4/19
 * Time: 1:39 AM
 */

namespace Aligent\BraintreeBundle\Method\Provider;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Aligent\BraintreeBundle\Method\Config\Provider\BraintreeConfigProviderInterface;
use Aligent\BraintreeBundle\Method\Factory\BraintreeMethodFactoryInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\AbstractPaymentMethodProvider;

class BraintreeMethodProvider extends AbstractPaymentMethodProvider
{
    /**
     * @var BraintreeMethodFactoryInterface
     */
    protected $factory;

    /**
     * @var BraintreeConfigProviderInterface
     */
    protected $configProvider;


    /**
     * @param BraintreeConfigProviderInterface $configProvider
     * @param BraintreeMethodFactoryInterface $factory
     */
    public function __construct(
        BraintreeConfigProviderInterface $configProvider,
        BraintreeMethodFactoryInterface $factory
    ) {
        parent::__construct();

        $this->configProvider = $configProvider;
        $this->factory = $factory;
    }

    /**
     * Save methods to $methods property
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