<?php

namespace Entrepids\Bundle\BraintreeBundle\Form\Type;

use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;
use Entrepids\Bundle\BraintreeBundle\Settings\DataProvider\CardTypesDataProviderInterface;
use Entrepids\Bundle\BraintreeBundle\Settings\DataProvider\PaymentActionsDataProviderInterface;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class BraintreeSettingsType extends AbstractType
{
    const BLOCK_PREFIX = 'braintree_settings';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var SymmetricCrypterInterface
     */
    protected $encoder;

    /**
     * @var CardTypesDataProviderInterface
     */
    private $cardTypesDataProvider;

    /**
     * @var PaymentActionsDataProviderInterface
     */
    private $paymentActionsDataProvider;

    /**
     * @param TranslatorInterface                 $translator
     * @param SymmetricCrypterInterface           $encoder
     * @param CardTypesDataProviderInterface      $cardTypesDataProvider
     * @param PaymentActionsDataProviderInterface $paymentActionsDataProvider
     */
    public function __construct(
        TranslatorInterface $translator,
        SymmetricCrypterInterface $encoder,
        CardTypesDataProviderInterface $cardTypesDataProvider,
        PaymentActionsDataProviderInterface $paymentActionsDataProvider
    ) {
        $this->translator = $translator;
        $this->encoder = $encoder;
        $this->cardTypesDataProvider = $cardTypesDataProvider;
        $this->paymentActionsDataProvider = $paymentActionsDataProvider;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     * @throws \InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $a=1;
    	$builder
            ->add('braintreeLabel', LocalizedFallbackValueCollectionType::NAME, [
                'label' => 'entrepids.braintree.settings.credit_card_labels.label',
                'required' => true,
                'options' => ['constraints' => [new NotBlank()]],
            ])
            ->add('braintreeShortLabel', LocalizedFallbackValueCollectionType::NAME, [
                'label' => 'entrepids.braintree.settings.credit_card_short_labels.label',
                'required' => true,
                'options' => ['constraints' => [new NotBlank()]],
            ])
            ->add('braintreePaymentAction', ChoiceType::class, [
                'choices' => $this->paymentActionsDataProvider->getPaymentActions(),
                'choices_as_values' => true,
                'choice_label' => function ($action) {
                    return $this->translator->trans(
                        sprintf('entrepids.braintree.settings.payment_action.%s', $action)
                    );
                },
                'label' => 'entrepids.braintree.settings.credit_card_payment_action.label',
                'required' => true,
            ])
            ->add('braintreeCaptureAction', ChoiceType::class, [
            		'choices' => $this->paymentActionsDataProvider->getCaptureActions(),
            		'choices_as_values' => true,
            		'choice_label' => function ($action) {
            			return $this->translator->trans(
            					sprintf('entrepids.braintree.settings.capture.capture_action.%s', $action)
            			);
            		},
            		'label' => 'entrepids.braintree.settings.capture.capture_action.label',
            		'required' => true,
            		])            
            ->add('allowedCreditCardTypes', ChoiceType::class, [
                'choices' => $this->cardTypesDataProvider->getCardTypes(),
                'choices_as_values' => true,
                'choice_label' => function ($cardType) {
                    return $this->translator->trans(
                        sprintf('entrepids.braintree.settings.allowed_cc_types.%s', $cardType)
                    );
                },
                'label' => 'entrepids.braintree.settings.allowed_cc_types.label',
                'required' => true,
                'multiple' => true,
            ])
            ->add('braintreeEnvironmentType', ChoiceType::class, [
            		'choices' => $this->cardTypesDataProvider->getEnvironmentType(),
            		'choices_as_values' => true,
            		'choice_label' => function ($cardType) {
            			return $this->translator->trans(
            					sprintf('entrepids.braintree.settings.environment_types.%s', $cardType)
            			);
            		},
            		'label' => 'entrepids.braintree.settings.environment_types.label',
            		'required' => true,
            		])            
            
            ->add('braintreeMerchId', TextType::class, [
                'label' => 'entrepids.braintree.settings.merch_id.label',
                'required' => true,
            ])
            ->add('braintreeMerchAccountId', TextType::class, [
                'label' => 'entrepids.braintree.settings.merch_account_id.label',
                'required' => true,
            ])
            ->add('braintreeMerchPublicKey', TextType::class, [
                'label' => 'entrepids.braintree.settings.public_key.label',
                'required' => true,
            ])
            ->add('braintreeMerchPrivateKey', TextType::class, [
                'label' => 'entrepids.braintree.settings.private_key.label',
                'required' => true,
            ])
            ->add('creditCardEnabled', CheckboxType::class, [
                'label' => 'entrepids.braintree.settings.credit_card_enabled.label',
                'required' => false,
            ])
            ->add('saveForLater', CheckboxType::class, [
                'label' => 'entrepids.braintree.settings.save_for_later.label',
                'required' => false,
            ])
            ->add('newOrderStatus', TextType::class, [
            		'label' => 'entrepids.braintree.settings.new_order_status.label',
            		'required' => true,
            ])
            ->add('savedCards', CheckboxType::class, [
                'label' => 'entrepids.braintree.settings.saved_cards.label',
                'required' => false,
            ])
            ->add('cvvVerification', CheckboxType::class, [
                'label' => 'entrepids.braintree.settings.cvv_verification.label',
                'required' => false,
            ])
            ->add('displayCardTypes', CheckboxType::class, [
            		'label' => 'entrepids.braintree.settings.display_card_types.label',
            		'required' => false,
            ]);            
        /*$this->transformWithEncodedValue($builder, 'vendor');
        $this->transformWithEncodedValue($builder, 'partner');
        $this->transformWithEncodedValue($builder, 'user');
        $this->transformWithEncodedValue($builder, 'password', false);
        $this->transformWithEncodedValue($builder, 'proxyHost');
        $this->transformWithEncodedValue($builder, 'proxyPort');*/
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    	$a = 1;
        $resolver->setDefaults([
            'data_class' => BraintreeSettings::class,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string               $field
     * @param bool                 $decrypt
     *
     * @throws \InvalidArgumentException
     */
    protected function transformWithEncodedValue(FormBuilderInterface $builder, $field, $decrypt = true)
    {
        $builder->get($field)->addModelTransformer(new CallbackTransformer(
            function ($value) use ($decrypt) {
                if ($decrypt === true) {
                    return $this->encoder->decryptData($value);
                }

                return $value;
            },
            function ($value) {
                return $this->encoder->encryptData($value);
            }
        ));
    }
}