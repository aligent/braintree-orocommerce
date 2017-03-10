<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryLevelStub extends InventoryLevel
{
    protected $warehouse;

    /**
     * @return mixed
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param mixed $warehouse
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;
    }
}
