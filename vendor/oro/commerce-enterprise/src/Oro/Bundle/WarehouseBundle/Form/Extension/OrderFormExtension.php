<?php

namespace Oro\Bundle\WarehouseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\OrderBundle\Form\Type\OrderType;
use Oro\Bundle\WarehouseBundle\Entity\Helper\WarehouseCounter;

class OrderFormExtension extends AbstractTypeExtension
{
    /**
     * @var WarehouseCounter
     */
    private $warehouseCounter;

    /**
     * @param WarehouseCounter $warehouseCounter
     */
    public function __construct(WarehouseCounter $warehouseCounter)
    {
        $this->warehouseCounter = $warehouseCounter;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OrderType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->warehouseCounter->areMoreWarehouses()) {
            $builder->remove('warehouse');
        }
    }
}
