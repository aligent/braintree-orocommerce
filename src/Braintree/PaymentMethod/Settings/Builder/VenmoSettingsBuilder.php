<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/10/19
 * Time: 8:21 PM
 */

namespace Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder;


use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class VenmoSettingsBuilder implements SettingsBuilderInterface
{

    /**
     * Build the settings object to pass to Dropin
     * @param PaymentContextInterface $context
     * @param array $settings
     * @return mixed
     */
    public function build(PaymentContextInterface $context, array $settings)
    {
        // Never allow new browser tab as it isn't supported by our checkout flow yet.
        // TODO: Make this configurable, will require implementing some event listeners and changing our js
        return [
            'allowNewBrowserTab' => false
        ];
    }
}