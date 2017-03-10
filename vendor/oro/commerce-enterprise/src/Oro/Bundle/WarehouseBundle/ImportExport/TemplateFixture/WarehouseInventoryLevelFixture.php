<?php

namespace Oro\Bundle\WarehouseBundle\ImportExport\TemplateFixture;

use Oro\Bundle\InventoryBundle\ImportExport\TemplateFixture\InventoryLevelFixture as BaseFixture;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;

class WarehouseInventoryLevelFixture extends BaseFixture
{
    /**
     * {@inheritdoc}
     */
    public function fillEntityData($key, $entity)
    {
        parent::fillEntityData($key, $entity);

        $warehouse = new Warehouse();
        $warehouse->setName('First Warehouse');
        $entity->setWarehouse($warehouse);
    }
}
