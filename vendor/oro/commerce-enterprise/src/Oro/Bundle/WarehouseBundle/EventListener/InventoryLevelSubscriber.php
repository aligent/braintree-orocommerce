<?php

namespace Oro\Bundle\WarehouseBundle\EventListener;

use Oro\Bundle\ImportExportBundle\Event\DenormalizeEntityEvent;
use Oro\Bundle\ImportExportBundle\Event\NormalizeEntityEvent;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;

class InventoryLevelSubscriber
{
    /**
     * @param NormalizeEntityEvent $event
     */
    public function afterNormalize(NormalizeEntityEvent $event)
    {
        if (!$event->getObject() instanceof InventoryLevel) {
            return;
        }

        if ($event->getObject() && $event->getObject()->getWarehouse()) {
            $event->setResultField(
                'warehouse',
                ['name' => $event->getObject()->getWarehouse()->getName()]
            );
        }
    }

    /**
     * @param DenormalizeEntityEvent $event
     */
    public function afterDenormalize(DenormalizeEntityEvent $event)
    {
        if (!$event->getObject() instanceof InventoryLevel) {
            return;
        }

        $data = $event->getData();
        if (isset($data['warehouse'])) {
            $warehouse = new Warehouse();
            $warehouse->setName($data['warehouse']['name']);
            $event->getObject()->setWarehouse($warehouse);
        }
    }
}
