<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/10/19
 * Time: 6:50 PM
 */

namespace Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder;


use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

interface SettingsBuilderInterface
{

    /**
     * Build the settings object to pass to Dropin
     * @param PaymentContextInterface $context
     * @param array $settings
     * @return mixed
     */
    public function build(PaymentContextInterface $context, array $settings);
}