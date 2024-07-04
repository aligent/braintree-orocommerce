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
use Oro\Bundle\PricingBundle\SubtotalProcessor\TotalProcessorProvider;

class ApplePayConfigurationBuilder implements ConfigurationBuilderInterface, FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

    protected TotalProcessorProvider $totalsProvider;

    /**
     * PayPalCreditSettingsBuilder constructor.
     */
    public function __construct(TotalProcessorProvider $totalsProvider)
    {
        $this->totalsProvider = $totalsProvider;
    }

    /**
     * Build the settings object to pass to Dropin
     */
    public function build(PaymentContextInterface $context, array $configuration): mixed
    {
        if (!$this->isFeaturesEnabled()) {
            return null;
        }

        // Strip Null values
        $viewSettings = array_filter(
            $configuration,
            function ($value) {
                return $value !== null;
            }
        );

        $total = $this->totalsProvider->getTotal($context->getSourceEntity());

        return array_merge(
            $viewSettings,
            [
                'paymentRequest' => [
                    'total' => [
                        'label'  => $viewSettings['displayName'], // TODO: Turn this into it's own config option,
                        'amount' => $total->getAmount()
                    ],
                    'requiredBillingContactFields' => ['postalAddress'] //TODO: Allow more values here
                ]
            ]
        );
    }
}
