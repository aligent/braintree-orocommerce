<?php

namespace Oro\Bundle\WarehouseBundle\ImportExport\Strategy;

use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\InventoryBundle\ImportExport\Strategy\AbstractInventoryLevelStrategyHelper;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnitPrecision;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;

class InventoryLevelStrategyHelper extends AbstractInventoryLevelStrategyHelper
{
    /**
     * {@inheritdoc}
     */
    public function process(
        InventoryLevel $importedEntity,
        array $importData = [],
        array $newEntities = [],
        array $errors = []
    ) {
        $this->errors = $errors;

        $existingWarehouse = $this->getProcessedEntity($newEntities, 'warehouse');
        if (!$existingWarehouse) {
            $existingWarehouse = new Warehouse();
        }

        $product = $this->getProcessedEntity($newEntities, 'product');
        if (!$product) {
            // The product should exist, otherwise an error should have been added in InventoryStatusesStrategyHelper,
            // but if in any case the product is null at this step, this import entry should not be processed
            return;
        }

        $productUnitPrecision = $this->getProcessedEntity($newEntities, 'productUnitPrecision');
        if (!$productUnitPrecision) {
            // Same as the product comment above
            return;
        }

        /** @var InventoryLevel $existingEntity */
        $existingEntity = $this->getExistingInventoryLevel(
            $product,
            $productUnitPrecision,
            $existingWarehouse
        );

        $existingEntity->setProductUnitPrecision($productUnitPrecision);
        $existingEntity->setWarehouse($existingWarehouse);
        $existingEntity->setQuantity($importedEntity->getQuantity());

        $newEntities['inventoryLevel'] = $existingEntity;
        if ($this->successor) {
            return $this->successor->process($importedEntity, $importData, $newEntities, $this->errors);
        }

        return $existingEntity;
    }

    /**
     * Retrieves the existing, if any, InventoryLevel entity base on the Product,
     * ProductUnitPrecision and/or Warehouse
     *
     * @param Product $product
     * @param ProductUnitPrecision $productUnitPrecision
     * @param Warehouse $warehouse
     * @return null|InventoryLevel
     */
    protected function getExistingInventoryLevel(
        Product $product,
        ProductUnitPrecision $productUnitPrecision,
        Warehouse $warehouse = null
    ) {
        $criteria = [
            'product' => $product,
            'productUnitPrecision' => $productUnitPrecision
        ];

        if ($warehouse) {
            $criteria['warehouse'] = $warehouse;
        }

        $existingEntity = $this->databaseHelper->findOneBy(InventoryLevel::class, $criteria);

        if (!$existingEntity) {
            $existingEntity = new InventoryLevel();
        }

        return $existingEntity;
    }
}
