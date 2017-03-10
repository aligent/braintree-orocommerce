<?php

namespace Oro\Bundle\MultiWebsiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\ValidationBundle\Validator\Constraints\Integer;

class WebsiteMatcherSettingsType extends AbstractType
{
    const NAME = 'oro_multiwebsite_matcher_settings';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'enabled',
                CheckboxType::class
            )
            ->add(
                'matcher_alias',
                HiddenType::class,
                [
                    'required' => true
                ]
            )
            ->add(
                'priority',
                IntegerType::class,
                [
                    'required' => true,
                    'constraints' => [new NotBlank(), new Integer()]
                ]
            );
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
