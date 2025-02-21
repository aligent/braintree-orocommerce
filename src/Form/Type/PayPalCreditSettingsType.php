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

use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PayPalCreditSettingsType extends AbstractType
{
    protected \Symfony\Contracts\Translation\TranslatorInterface $translator;

    protected array $locales;

    /**
     * PayPalSettingsType constructor.
     */
    public function __construct(\Symfony\Contracts\Translation\TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'aligent.braintree.settings.paypal_credit.enabled'
                ]
            )
            ->add(
                'flow',
                ChoiceType::class,
                [
                    'label'    =>  'aligent.braintree.settings.paypal_credit.flow.label',
                    'required' => false,
                    'choices'  => [
                        $this->translator->trans('aligent.braintree.settings.paypal_credit.flow.checkout.label')
                            => BraintreeConfigInterface::PAYPAL_FLOW_CHECKOUT,
                        $this->translator->trans('aligent.braintree.settings.paypal_credit.flow.vault.label')
                            => BraintreeConfigInterface::PAYPAL_FLOW_VAULT
                    ],
                    'empty_data' => BraintreeConfigInterface::PAYPAL_FLOW_VAULT
                ]
            );
    }
}
