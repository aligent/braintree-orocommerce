<?php

namespace Oro\Bundle\WarehouseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\WarehouseBundle\Form\Type\WarehouseAddressType;

class WarehouseAddressExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return WarehouseAddressType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'system',
            CheckboxType::class,
            [
                'label' => 'oro.warehouse.use_system_configuration',
                'required' => false,
            ]
        );
    }
}
