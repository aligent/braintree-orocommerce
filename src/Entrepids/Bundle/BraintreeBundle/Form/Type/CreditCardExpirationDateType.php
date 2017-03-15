<?php

namespace Entrepids\Bundle\BraintreeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardExpirationDateType extends AbstractType
{
    const NAME = 'entrepids_braintree_credit_card_expiration_date';
    const YEAR_PERIOD = 10;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       // $builder->remove('day');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $months = array_map(function ($value) {
            return sprintf('%02d', $value);
        }, range(1, 12));

        $resolver->setDefaults(
            [
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
                'format' => 'dMy',
                'input' => 'array',
                'years' => range(date('y'), date('y') + self::YEAR_PERIOD),
                'months' => $months
            ]
        );

        $resolver->setAllowedValues(['input' => ['array']]);
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

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'date';
    }
}
