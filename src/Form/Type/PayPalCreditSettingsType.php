<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/9/19
 * Time: 7:55 PM
 */

namespace Aligent\BraintreeBundle\Form\Type;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PayPalCreditSettingsType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var array
     */
    protected $locales;

    /**
     * PayPalSettingsType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
                        $this->translator->trans('aligent.braintree.settings.paypal_credit.flow.checkout.label') => BraintreeConfig::PAYPAL_FLOW_CHECKOUT,
                        $this->translator->trans('aligent.braintree.settings.paypal_credit.flow.vault.label')    => BraintreeConfig::PAYPAL_FLOW_VAULT
                    ],
                    'empty_data' => BraintreeConfig::PAYPAL_FLOW_VAULT
                ]
            );
    }
}