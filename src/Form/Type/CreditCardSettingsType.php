<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/9/19
 * Time: 7:55 PM
 */

namespace Aligent\BraintreeBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class CreditCardSettingsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'cardholderName',
            CheckboxType::class,
            [
                'label'    => 'aligent.braintree.settings.credit_card.require_name.label',
                'required' => false,
            ]
        )
        ->add(
            'enabled',
            HiddenType::class,
            [
                'data' => true
            ]
        );
    }
}