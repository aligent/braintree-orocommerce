<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Form\Type;

use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PaymentMethodSettingsType extends AbstractType
{
    protected FeatureChecker $featureChecker;

    /**
     * PaymentMethodSettingsType constructor.
     */
    public function __construct(FeatureChecker $featureChecker)
    {
        $this->featureChecker = $featureChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'card',
            CreditCardSettingsType::class,
            [
                'label'    => 'aligent.braintree.settings.credit_card.label',
                'required' => false
            ]
        )->add(
            'paypal',
            PayPalSettingsType::class,
            [
                'label' => 'aligent.braintree.settings.paypal.label',
                'required' => false
            ]
        );

        if ($this->featureChecker->isFeatureEnabled('experimental_payment_methods')) {
            $builder->add(
                'paypalCredit',
                PayPalCreditSettingsType::class,
                [
                    'label' => 'aligent.braintree.settings.paypal_credit.label',
                    'required' => false
                ]
            )->add(
                'venmo',
                VenmoSettingsType::class,
                [
                    'label' => 'aligent.braintree.settings.venmo.label',
                    'required' => false
                ]
            )->add(
                'googlePay',
                GooglePaySettingsType::class,
                [
                    'label' => 'aligent.braintree.settings.google_pay.label',
                    'required' => false
                ]
            )->add(
                'applePay',
                ApplePaySettingsType::class,
                [
                    'label' => 'aligent.braintree.settings.apple_pay.label',
                    'required' => false
                ]
            );
        }
    }
}
