<?php

namespace Entrepids\Bundle\BraintreeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\ValidationBundle\Validator\Constraints\Integer;

class CreditCardType extends AbstractType
{
    const NAME = 'entrepids_braintree_credit_card';

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'ACCT',
            'text',
            [
                'required' => true,
                'label' => 'entrepids.braintree.credit_card.card_number.label',
                'mapped' => false,
                'attr' => [
                    'data-validation' => [
                        'credit-card-number' => [
                            'message' => 'entrepids.braintree.validation.credit_card',
                            'payload' => null,
                        ],
                        'credit-card-type' => [
                            'message' => 'entrepids.braintree.validation.credit_card_type',
                            'payload' => null,
                        ]
                    ],
                    'data-credit-card-type-validator' => 'credit-card-type',
                    'data-card-number' => true,
                    'autocomplete' => 'off',
                    'data-gateway' => true,
                    'placeholder' => false,
                ],
                'constraints' => [
                    new Integer(),
                    new NotBlank(),
                    new Length(['min' => '12', 'max' => '19']),
                ],
            ]
        )->add(
            'expirationDate',
            'entrepids_braintree_credit_card_expiration_date',
            [
                'required' => true,
                'label' => 'entrepids.braintree.credit_card.expiration_date.label',
                'mapped' => false,
                'placeholder' => [
                    'year' => 'Year',
                    'month' => 'Month',
                ],
                'attr' => [
                    'data-expiration-date' => true,
                ],
            ]
        )->add(
            'EXPDATE',
            'hidden',
            [
                'attr' => [
                    'data-gateway' => true,
                ],
            ]
        )
        ->add(
        		'paymethod_nonce',
        		'hidden'
        );

        if ($options['requireCvvEntryEnabled']) {
            $builder->add(
                'CVV2',
                'password',
                [
                    'required' => true,
                    'label' => 'entrepids.braintree.credit_card.cvv2.label',
                    'mapped' => false,
                    'block_name' => 'payment_credit_card_cvv',
                    'constraints' => [
                        new Integer(['message' => 'oro.payment.number.error']),
                        new NotBlank(),
                        new Length(['min' => 3, 'max' => 4]),
                    ],
                    'attr' => [
                        'data-card-cvv' => true,
                        'data-gateway' => true,
                        'placeholder' => false,
                    ],
                ]
            );
        }
		$builder->add ( 'save_for_later', 'checkbox', [ 
				'required' => false,
				'label' => 'entrepids.braintree.credit_card.save_for_later.label',
				'mapped' => false,
				'data' => true,
				'attr' => [ 
						'data-save-for-later' => true 
				] 
		] );

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'entrepids.braintree.methods.credit_card.label',
            'csrf_protection' => false,
            'zeroAmountAuthorizationEnabled' => false,
            'requireCvvEntryEnabled' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children as $child) {
            $child->vars['full_name'] = $child->vars['name'];
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
