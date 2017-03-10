<?php

namespace Oro\Bundle\WarehouseBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;

class InventoryLevelGridListener
{
    const WAREHOUSE_NAME_COLUMN_NAME = 'warehouseName';
    const WAREHOUSE_NAME_COLUMN_LABEL = 'oro.warehouse.entity_label';

    /**
     * @param BuildBefore $event
     */
    public function onBuildBeforeWidget(BuildBefore $event)
    {
        $config = $event->getConfig();
        $query = $config->getOrmQuery();

        // add inner join config of warehouse
        $query->addInnerJoin(
            Warehouse::class,
            'warehouse',
            'WITH',
            'IDENTITY(precision.product) = :productId'
        );

        // add left join condition
        $leftJoins = $query->getLeftJoins();
        foreach ($leftJoins as $index => $join) {
            if ($join['alias'] === 'level') {
                $leftJoins[$index]['condition'] = $join['condition'] . ' AND level.warehouse = warehouse';
            }
        }
        $query->setLeftJoins($leftJoins);

        // add select for warehouse
        $selects = $query->getSelect();
        foreach ($selects as $index => $select) {
            if (strpos($select, 'combinedId') !== false) {
                $selects[$index] = 'CONCAT(warehouse.id, \'_\', precision.id) as combinedId';
            }
        }
        $selects[] = 'warehouse.name as ' . self::WAREHOUSE_NAME_COLUMN_NAME;
        $query->setSelect($selects);

        //add warehouse column
        $columns = $config->offsetGetByPath('[columns]');
        $warehouseColumn = [
            self::WAREHOUSE_NAME_COLUMN_NAME => [
                'label' => self::WAREHOUSE_NAME_COLUMN_LABEL
            ],
        ];
        $columns = array_merge($warehouseColumn, $columns);
        $config->offsetSetByPath('[columns]', $columns);
    }
}
