<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 2:02 AM
 */

namespace Aligent\BraintreeBundle\Form\Type;


use Aligent\BraintreeBundle\Braintree\Gateway;
use Aligent\BraintreeBundle\Entity\BraintreeIntegrationSettings;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BraintreeIntegrationSettingsType extends AbstractType
{

    const BLOCK_PREFIX = 'aligent_braintree_settings_type';

    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * BraintreeIntegrationSettingsType constructor.
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
                'labels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'aligent.braintree.settings.labels.label',
                    'required' => true,
                    'constraints' => [new Assert\NotBlank()]
                ]
            )
            ->add(
                'shortLabels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'aligent.braintree.settings.short_labels.label',
                    'required' => true,
                    'constraints' => [new Assert\NotBlank()]
                ]
            )
            ->add(
                'environment',
                ChoiceType::class,
                [
                    'label' => 'aligent.braintree.settings.environment.label',
                    'required' => true,
                    'constraints' => [new Assert\NotBlank()],
                    'choices' => [
                        Gateway::SANDBOX,
                        Gateway::PRODUCTION
                    ],
                    'choices_as_values' => true,
                    'choice_label' => function ($environment) {
                        return $this->translator->trans(
                            sprintf(
                                'aligent.braintree.settings.%s.label',
                                $environment
                            )
                        );
                    }
                ]
            )
            ->add(
                'merchantId',
                TextType::class,
                [
                    'label' => 'aligent.braintree.settings.merchant_id.label',
                    'required' => true,
                    'constraints' => [new Assert\NotBlank()]
                ]
            )
            ->add(
                'merchantAccountId',
                TextType::class,
                [
                    'label' => 'aligent.braintree.settings.merchant_account_id.label',
                    'required' => true,
                    'constraints' => [new Assert\NotBlank()]
                ]
            )
            ->add(
                'publicKey',
                OroEncodedPlaceholderPasswordType::class,
                [
                    'label' => 'aligent.braintree.settings.public_key.label',
                    'required' => true,
                    'constraints' => [new Assert\NotBlank()]
                ]
            )
            ->add(
                'privateKey',
                OroEncodedPlaceholderPasswordType::class,
                [
                    'label' => 'aligent.braintree.settings.private_key.label',
                    'required' => true,
                    'constraints' => [new Assert\NotBlank()]
                ]
            )
            ->add(
                'vaultMode',
                CheckboxType::class,
                [
                    'label' => 'aligent.braintree.settings.vault_mode.label'
                ]
            )
            ->add(
                'paymentMethodSettings',
                PaymentMethodSettingsType::class,
                [
                    'label'    => 'aligent.braintree.settings.payment_method_settings.label',
                    'required' => false,
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => BraintreeIntegrationSettings::class
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        static::BLOCK_PREFIX;
    }
}