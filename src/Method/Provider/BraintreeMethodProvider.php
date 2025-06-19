<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\Provider;

use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Aligent\BraintreeBundle\Method\Config\Provider\BraintreeConfigProviderInterface;
use Aligent\BraintreeBundle\Method\Factory\BraintreeMethodFactoryInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\AbstractPaymentMethodProvider;

class BraintreeMethodProvider extends AbstractPaymentMethodProvider
{
    protected BraintreeMethodFactoryInterface $factory;
    protected BraintreeConfigProviderInterface $configProvider;

    public function __construct(
        BraintreeConfigProviderInterface $configProvider,
        BraintreeMethodFactoryInterface $factory,
    ) {
        parent::__construct();

        $this->configProvider = $configProvider;
        $this->factory = $factory;
    }

    /**
     * Save methods to $methods property
     */
    protected function collectMethods(): void
    {
        foreach ($this->configProvider->getPaymentConfigs() as $config) {
            $this->addBraintreeMethod($config);
        }
    }

    /**
     * @param BraintreeConfigInterface $config
     */
    protected function addBraintreeMethod(BraintreeConfigInterface $config): void
    {
        $this->addMethod(
            $config->getPaymentMethodIdentifier(),
            $this->factory->create($config)
        );
    }
}
