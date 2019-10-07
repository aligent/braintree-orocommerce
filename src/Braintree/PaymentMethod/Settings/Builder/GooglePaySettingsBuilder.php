<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/10/19
 * Time: 8:35 PM
 */

namespace Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder;


use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PricingBundle\SubtotalProcessor\TotalProcessorProvider;

class GooglePaySettingsBuilder implements SettingsBuilderInterface
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
     * Build the settings object to pass to Dropin
     * @param PaymentContextInterface $context
     * @param array $settings
     * @return mixed
     */
    public function build(PaymentContextInterface $context, array $settings)
    {
        // Strip Null values
        $viewSettings = array_filter(
            $settings,
            function ($value) {
                return $value !== NULL;
            }
        );

        $total = $this->totalsProvider->getTotal($context->getSourceEntity());

        return array_merge(
            $viewSettings,
            [
                'transactionInfo' => [
                    'totalPriceStatus' => 'FINAL', // TODO: Support the other statuses
                    'totalPrice' => $total->getAmount(),
                    'currencyCode' => $total->getCurrency()
                ],
                'cardRequirements' => [
                    'billingAddressRequired' => true // TODO: Make this configurable and allow additional card requirements
                ]
            ]
        );
    }
}