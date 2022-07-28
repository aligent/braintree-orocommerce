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

use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class VenmoConfigurationBuilder implements ConfigurationBuilderInterface, FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

    /**
     * {@inheritdoc}
     */
    public function build(PaymentContextInterface $context, array $configuration): array
    {
        if (!$this->isFeaturesEnabled()) {
            return [];
        }

        // Never allow new browser tab as it isn't supported by our checkout flow yet.
        // TODO: Make this configurable, will require implementing some event listeners and changing our js
        return [
            'allowNewBrowserTab' => false
        ];
    }
}
