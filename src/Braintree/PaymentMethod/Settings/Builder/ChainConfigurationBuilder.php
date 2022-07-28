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
    /** @var ConfigurationBuilderInterface[] */
    protected array $builders = [];

    /**
     * {@inheritDoc}
     */
    public function build(PaymentContextInterface $context, array $configuration): array
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
            } elseif ($paymentMethod === 'card') {
                // handle the card special case, as card is the default it must be explicitly
                // set to false, so it doesn't display
                $config[$paymentMethod] = false;
            }
        }

        return $config;
    }

    public function hasBuilder(string $method): bool
    {
        return isset($this->builders[$method]);
    }

    public function getBuilder(string $method): ConfigurationBuilderInterface
    {
        if (!$this->hasBuilder($method)) {
            throw new \InvalidArgumentException("Builder for {$method} does not exist.");
        }

        return $this->builders[$method];
    }

    public function addBuilder(string $method, ConfigurationBuilderInterface $builder): self
    {
        if ($this->hasBuilder($method)) {
            throw new \InvalidArgumentException("Builder for {$method} already exists");
        }

        $this->builders[$method] = $builder;

        return $this;
    }
}
