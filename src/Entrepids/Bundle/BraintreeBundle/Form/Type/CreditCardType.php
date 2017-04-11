<?php

namespace Entrepids\Bundle\BraintreeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;



class CreditCardType extends AbstractType
{
    const NAME = 'entrepids_braintree_credit_card';

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
        	'payment_method_nonce',
        	'hidden',
            [
            		'mapped' => true,
                'attr' => [
                    'data-gateway' => true,
                ],
            ]
        );
		
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
