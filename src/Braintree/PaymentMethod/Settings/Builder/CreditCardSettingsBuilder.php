<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/10/19
 * Time: 7:28 PM
 */

namespace Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder;


use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class CreditCardSettingsBuilder implements SettingsBuilderInterface
{

    /**
     * Form and Card settings match 1 to 1 so nothing to do
     * @inheritdoc
     */
    public function build(PaymentContextInterface $context, array $settings)
    {
        return $settings;
    }
}