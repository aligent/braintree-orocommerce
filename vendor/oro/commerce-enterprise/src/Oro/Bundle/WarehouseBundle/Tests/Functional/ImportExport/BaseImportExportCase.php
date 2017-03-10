<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Functional\ImportExport;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\InventoryBundle\Tests\Functional\ImportExport\AbstractImportExportTestCase;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnitPrecision;
use Oro\Bundle\WarehouseBundle\Entity\Repository\WarehouseRepository;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;

abstract class BaseImportExportCase extends AbstractImportExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getFieldMappings()
    {
        return [
            'SKU' => 'product:sku',
            'Inventory Status' => 'product:inventoryStatus:name',
            'Quantity' => 'quantity',
            'Warehouse' => 'warehouse:name',
            'Unit' => 'productUnitPrecision:unit:code'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getInventoryLevelEntity($values = [])
    {
        /** @var EntityRepository $productRepository */
        $productRepository = $this->client->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository(Product::class);

        /** @var EntityRepository $productUnitPrecisionRepository */
        $productUnitPrecisionRepository = $this->client->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository(ProductUnitPrecision::class);

        /** @var WarehouseRepository $warehouseRepository */
        $warehouseRepository = $this->client->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository(Warehouse::class);

        /** @var EntityRepository $warehouseInventoryRepository */
        $warehouseInventoryRepository = $this->client->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository(InventoryLevel::class);

        $product = $productRepository->findOneBy(['sku' => $values['SKU']]);

        $warehouse = isset($values['Warehouse']) ? $values['Warehouse'] : null;
        if (!$warehouse) {
            $warehouse = $warehouseRepository->getSingularWarehouse();
        } else {
            $warehouse = $warehouseRepository->findOneBy(['name' => $warehouse]);
        }

        $unit = isset($values['Unit']) ? $values['Unit'] : null;
        if (!$unit) {
            $productUnitPrecision = $product->getPrimaryUnitPrecision();
        } else {
            $productUnitPrecision = $productUnitPrecisionRepository->findOneBy(
                [
                    'product' => $product,
                    'unit' => $unit
                ]
            );
        }

        return $warehouseInventoryRepository->findOneBy(
            [
                'product' => $product,
                'warehouse' => $warehouse,
                'productUnitPrecision' => $productUnitPrecision
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilePath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }
}
