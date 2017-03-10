<?php

namespace Oro\Bundle\WarehouseBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\WarehouseBundle\Entity\Helper\WarehouseCounter;

class OrderWarehouseGridListener
{
    /**
     * @var WarehouseCounter
     */
    protected $warehouseCounter;

    /**
     * @param WarehouseCounter $warehouseCounter
     */
    public function __construct(WarehouseCounter $warehouseCounter)
    {
        $this->warehouseCounter = $warehouseCounter;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        if ($this->warehouseCounter->areMoreWarehouses()) {
            return;
        }

        $config = $event->getConfig();

        // add column to grid and hide it by default
        $config->offsetSetByPath(
            sprintf('[columns][%s]', 'warehouse'),
            [
                'renderable' => false,
                'manageable' => false,
            ]
        );
    }
}
