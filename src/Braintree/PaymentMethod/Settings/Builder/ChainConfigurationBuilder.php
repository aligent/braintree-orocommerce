<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/10/19
 * Time: 6:42 PM
 */

namespace Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder;

use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class ChainConfigurationBuilder implements ConfigurationBuilderInterface
{

    /**
     * @var ConfigurationBuilderInterface[] $builders
     */
    protected $builders = [];

    /**
     * Build the settings object to pass to the Drop-in UI
     * @param PaymentContextInterface $context
     * @param array $configuration
     * @return mixed
     */
    public function build(PaymentContextInterface $context, array $configuration)
    {
        $config = [];
        foreach ($configuration as $paymentMethod => $paymentMethodConfig) {
            if ($paymentMethodConfig['enabled']) {
                unset($paymentMethodConfig['enabled']);

                if ($this->hasBuilder($paymentMethod)) {
                    $builder = $this->getBuilder($paymentMethod);
                    $paymentMethodConfig = $builder->build($context, $paymentMethodConfig);
                }

                $config[$paymentMethod] = $paymentMethodConfig;
            } elseif (!$paymentMethodConfig['enabled'] && $paymentMethod === 'card') {
                // handle the card special case, as card is the default it must be explicitly set to false so it doesnt display
                $config[$paymentMethod] = false;
            }
        }

        return $config;
    }

    /**
     * @param $method
     * @return bool
     */
    public function hasBuilder($method)
    {
        return isset($this->builders[$method]);
    }

    /**
     * @param $method
     * @return ConfigurationBuilderInterface
     */
    public function getBuilder($method)
    {
        if (!$this->hasBuilder($method)) {
            throw new \InvalidArgumentException("Builder for {$method} does not exist.");
        }

        return $this->builders[$method];
    }

    /**
     * @param $method
     * @param ConfigurationBuilderInterface $builder
     * @return $this
     */
    public function addBuilder($method, ConfigurationBuilderInterface $builder)
    {
        if ($this->hasBuilder($method)) {
            throw new \InvalidArgumentException("Builder for {$method} already exists");
        }

        $this->builders[$method] = $builder;

        return $this;
    }
}