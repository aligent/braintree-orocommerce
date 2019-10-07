<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/9/19
 * Time: 7:56 PM
 */

namespace Aligent\BraintreeBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ApplePaySettingsType extends AbstractType
{

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
                    'label' => 'aligent.braintree.settings.apple_pay.enabled'
                ]
            )
            ->add(
                'displayName',
                TextType::class,
                [
                    'label' => 'aligent.braintree.settings.apple_pay.display_name.label',
                    'required' => false
                ]
            );
    }
}