<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/10/19
 * Time: 6:42 PM
 */

namespace Aligent\BraintreeBundle\Provider;


use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\SettingsBuilderInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class PaymentMethodSettingsProvider
{

    /**
     * @var SettingsBuilderInterface[] $builders
     */
    protected $builders = [];

    /**
     * @param string $method
     * @param PaymentContextInterface $context
     * @param array $settings
     * @return array
     */
    public function build($method, PaymentContextInterface $context, array $settings = [])
    {
        $builder = $this->getBuilder($method);

        return $builder->build($context, $settings);
    }

    /**
     * @param $method
     * @return SettingsBuilderInterface
     */
    public function getBuilder($method)
    {
        if (!isset($this->builders[$method])) {
            throw new \InvalidArgumentException("Builder for {$method} does not exist.");
        }

        return $this->builders[$method];
    }

    /**
     * @param $method
     * @param SettingsBuilderInterface $builder
     * @return $this
     */
    public function addBuilder($method, SettingsBuilderInterface $builder)
    {
        if (isset($this->builders[$method])) {
            throw new \InvalidArgumentException("Builder for {$method} already exists");
        }

        $this->builders[$method] = $builder;

        return $this;
    }
}