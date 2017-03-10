<?php

namespace Oro\Bundle\MultiCurrencyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\MultiCurrencyBundle\Form\Transformer\ArrayToJsonTransformer;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\Rates as RatesConstraint;

class CurrencyRatesType extends AbstractType
{
    const NAME = 'oro_currency_rates';
    const SETTING_NAME = 'currency_rates';
    const CONFIG_FORM_NAME = 'oro_multi_currency___currency_rates';
    

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ArrayToJsonTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new RatesConstraint()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\HiddenType';
    }

    /**
     * {@inheritdoc}
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
