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
use Symfony\Component\Form\FormBuilderInterface;

class VenmoSettingsType extends AbstractType
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
                    'label' => 'aligent.braintree.settings.venmo.enabled'
                ]
            );
    }
}