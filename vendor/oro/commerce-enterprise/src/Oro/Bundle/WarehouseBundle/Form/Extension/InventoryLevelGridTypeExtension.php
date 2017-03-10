<?php

namespace Oro\Bundle\WarehouseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\InventoryBundle\Form\Type\InventoryLevelGridType;
use Oro\Bundle\WarehouseBundle\Form\DataTransformer\InventoryLevelGridDataTransformer;

class InventoryLevelGridTypeExtension extends AbstractTypeExtension
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return InventoryLevelGridType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new InventoryLevelGridDataTransformer($this->doctrineHelper, $options['product']),
            true
        );
    }
}
