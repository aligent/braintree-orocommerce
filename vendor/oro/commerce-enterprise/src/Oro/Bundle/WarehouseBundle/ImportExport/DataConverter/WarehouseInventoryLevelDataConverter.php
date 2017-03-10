<?php

namespace Oro\Bundle\WarehouseBundle\ImportExport\DataConverter;

use Oro\Bundle\InventoryBundle\ImportExport\DataConverter\InventoryLevelDataConverter;

class WarehouseInventoryLevelDataConverter extends InventoryLevelDataConverter
{
    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        $header = parent::getHeaderConversionRules();

        return array_merge($header, ['Warehouse' => 'warehouse:name']);
    }
}
