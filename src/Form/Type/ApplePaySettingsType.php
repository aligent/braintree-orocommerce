<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ApplePaySettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
