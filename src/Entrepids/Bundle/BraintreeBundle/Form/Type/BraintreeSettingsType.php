<?php
namespace Entrepids\Bundle\BraintreeBundle\Form\Type;

use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;
use Entrepids\Bundle\BraintreeBundle\Settings\DataProvider\CardTypesDataProviderInterface;
use Entrepids\Bundle\BraintreeBundle\Settings\DataProvider\PaymentActionsDataProviderInterface;
use Entrepids\Bundle\BraintreeBundle\Settings\DataProvider\BasicEnvironmentDataProvider;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class BraintreeSettingsType extends AbstractType
{

    const BLOCK_PREFIX = 'entrepids_braintree_settings';

    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @var SymmetricCrypterInterface
     */
    protected $encoder;

    /**
     *
     * @var CardTypesDataProviderInterface
     */
    private $cardTypesDataProvider;

    /**
     *
     * @var PaymentActionsDataProviderInterface
     */
    private $paymentActionsDataProvider;
    
    /**
     *
     * @var BasicEnvironmentDataProvider
     */
    private $environmentProvider;

    /**
     *
     * @param TranslatorInterface $translator
     * @param SymmetricCrypterInterface $encoder
     * @param CardTypesDataProviderInterface $cardTypesDataProvider
     * @param PaymentActionsDataProviderInterface $paymentActionsDataProvider
     * @param BasicEnvironmentDataProvider $basicEnvironmentProvider
     */
    public function __construct(
        TranslatorInterface $translator,
        SymmetricCrypterInterface $encoder,
        CardTypesDataProviderInterface $cardTypesDataProvider,
        PaymentActionsDataProviderInterface $paymentActionsDataProvider,
        BasicEnvironmentDataProvider $basicEnvironmentProvider
    ) {
        $this->translator = $translator;
        $this->encoder = $encoder;
        $this->cardTypesDataProvider = $cardTypesDataProvider;
        $this->paymentActionsDataProvider = $paymentActionsDataProvider;
        $this->environmentProvider = $basicEnvironmentProvider;
    }

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     * @throws \InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $a = 1;
        $builder->add('braintreeLabel', LocalizedFallbackValueCollectionType::NAME, [
            'label' => 'entrepids.braintree.settings.credit_card_labels.label',
            'required' => true,
            'options' => [
                'constraints' => [
                    new NotBlank()
                ]
            ]
        ])
            ->add('braintreeShortLabel', LocalizedFallbackValueCollectionType::NAME, [
            'label' => 'entrepids.braintree.settings.credit_card_short_labels.label',
            'required' => true,
            'options' => [
                'constraints' => [
                    new NotBlank()
                ]
            ]
            ])
            ->add('braintreePaymentAction', ChoiceType::class, [
            'choices' => $this->paymentActionsDataProvider->getPaymentActions(),
            'choices_as_values' => true,
            'choice_label' => function ($action) {
                return $this->translator->trans(sprintf('entrepids.braintree.settings.payment_action.%s', $action));
            },
            'label' => 'entrepids.braintree.settings.credit_card_payment_action.label',
            'tooltip' => 'entrepids.braintree.settings.credit_card_payment_action.label.tooltip',
            'required' => true
            ])
            ->
        add('allowedCreditCardTypes', ChoiceType::class, [
            'choices' => $this->cardTypesDataProvider->getCardTypes(),
            'choices_as_values' => true,
            'choice_label' => function ($cardType) {
                return $this->translator->trans(sprintf('entrepids.braintree.settings.allowed_cc_types.%s', $cardType));
            },
            'label' => 'entrepids.braintree.settings.allowed_cc_types.label',
            'required' => true,
            'multiple' => true
        ])
            ->add('braintreeEnvironmentType', ChoiceType::class, [
            'choices' => $this->environmentProvider->getEnvironmentType(),
            'choices_as_values' => true,
            'choice_label' => function ($cardType) {
                return $this->translator->trans(
                    sprintf(
                        'entrepids.braintree.settings.environment_types.%s',
                        $cardType
                    )
                );
            },
            'label' => 'entrepids.braintree.settings.environment_types.label',
            'tooltip' => 'entrepids.braintree.settings.environment_types.label.tooltip',
            'required' => true
            ])
            ->
        add('braintreeMerchId', TextType::class, [
            'label' => 'entrepids.braintree.settings.merch_id.label',
            'tooltip' => 'entrepids.braintree.settings.merch_id.label.tooltip',
            'required' => true
        ])
            ->add('braintreeMerchAccountId', TextType::class, [
            'label' => 'entrepids.braintree.settings.merch_account_id.label',
            'tooltip' => 'entrepids.braintree.settings.merch_account_id.label.tooltip',
            'required' => true
            ])
            ->
        add('braintreeMerchPublicKey', OroEncodedPlaceholderPasswordType::class, [
            'label' => 'entrepids.braintree.settings.public_key.label',
            'tooltip' => 'entrepids.braintree.settings.public_key.label.tooltip',
            'required' => true
        ])
            ->add('braintreeMerchPrivateKey', OroEncodedPlaceholderPasswordType::class, [
            'label' => 'entrepids.braintree.settings.private_key.label',
            'tooltip' => 'entrepids.braintree.settings.private_key.label.tooltip',
            'required' => true
            ])
            ->add('saveForLater', CheckboxType::class, [
            'label' => 'entrepids.braintree.settings.save_for_later.label',
            'tooltip' => 'entrepids.braintree.settings.save_for_later.label.tooltip',
            'required' => false
            ]);
            
            
            $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
    }


    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var PayPalSettings|null $data */
        $data = $event->getData();
        if ($data && !$data->getAllowedCreditCardTypes()) {
            $data->setAllowedCreditCardTypes($this->cardTypesDataProvider->getDefaultCardTypes());
        }
    }
    
    /**
     *
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $a = 1;
        $resolver->setDefaults([
            'data_class' => BraintreeSettings::class
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
