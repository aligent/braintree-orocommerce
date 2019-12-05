<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/10/19
 * Time: 8:21 PM
 */

namespace Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder;


use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class VenmoConfigurationBuilder implements ConfigurationBuilderInterface, FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

    /**
     * Build the settings object to pass to Dropin
     * @param PaymentContextInterface $context
     * @param array $configuration
     * @return mixed
     */
    public function build(PaymentContextInterface $context, array $configuration)
    {
        if (!$this->isFeaturesEnabled()) {
            return;
        }

        // Never allow new browser tab as it isn't supported by our checkout flow yet.
        // TODO: Make this configurable, will require implementing some event listeners and changing our js
        return [
            'allowNewBrowserTab' => false
        ];
    }
}