<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder;

use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class ChainConfigurationBuilder implements ConfigurationBuilderInterface
{
    /**
     * @var ConfigurationBuilderInterface[] $builders
     */
    protected array $builders = [];

    /**
     * Build the settings object to pass to the Drop-in UI
     */
    public function build(PaymentContextInterface $context, array $configuration): mixed
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
                // handle the card special case, as card is the default it must be explicitly
                // set to false, so it doesn't display
                $config[$paymentMethod] = false;
            }
        }

        return $config;
    }

    /**
     * @param $method
     */
    public function hasBuilder($method): bool
    {
        return isset($this->builders[$method]);
    }

    /**
     * @param $method
     */
    public function getBuilder($method): ConfigurationBuilderInterface
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
