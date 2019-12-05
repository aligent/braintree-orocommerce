<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/10/19
 * Time: 8:01 PM
 */

namespace Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder;


use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PricingBundle\SubtotalProcessor\TotalProcessorProvider;

class PayPalConfigurationBuilder implements ConfigurationBuilderInterface
{

    /**
     * @var TotalProcessorProvider
     */
    protected $totalsProvider;

    /**
     * PayPalCreditSettingsBuilder constructor.
     * @param TotalProcessorProvider $totalsProvider
     */
    public function __construct(TotalProcessorProvider $totalsProvider)
    {
        $this->totalsProvider = $totalsProvider;
    }

        /**
     * @inheritdoc
     */
    public function build(PaymentContextInterface $context, array $configuration)
    {
        // Strip Null values
        $viewSettings = array_filter(
            $configuration,
            function ($value) {
                return $value !== NULL;
            }
        );

        // Checkout flow requires an amount and total
        if ($viewSettings['flow'] === 'checkout') {
            $total = $this->totalsProvider->getTotal($context->getSourceEntity());
            $viewSettings['amount'] = $total->getAmount();
            $viewSettings['currency'] = $total->getCurrency();
        }

        return $viewSettings;
    }
}