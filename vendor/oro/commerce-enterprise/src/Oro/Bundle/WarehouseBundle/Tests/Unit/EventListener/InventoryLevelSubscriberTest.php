<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Unit\EventListener;

use Oro\Bundle\ImportExportBundle\Event\NormalizeEntityEvent;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;
use Oro\Bundle\WarehouseBundle\EventListener\InventoryLevelSubscriber;
use Oro\Bundle\WarehouseBundle\Tests\Unit\Entity\Stub\InventoryLevelStub;

class InventoryLevelSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var  NormalizeEntityEvent|\PHPUnit_Framework_MockObject_MockObject */
    protected $event;

    /** @var InventoryLevelSubscriber  */
    protected $inventoryLevelSubscriber;

    protected function setUp()
    {
        $this->event = $this
            ->getMockBuilder(NormalizeEntityEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inventoryLevelSubscriber = new InventoryLevelSubscriber();
    }

    public function testNormalizeNoAction()
    {
        $this->event->expects($this->once())->method('getObject')->willReturn(null);
        $this->event->expects($this->never())->method('setResultField');

        $this->inventoryLevelSubscriber->afterNormalize($this->event);
    }

    public function testNormalize()
    {
        $object = $this->getInventoryLevelEntity(5, 'set', 'testName');

        $this->event->expects($this->any())->method('getObject')->willReturn($object);
        $this->event
            ->expects($this->once())
            ->method('setResultField')
            ->with(
                'warehouse',
                ['name' => $object->getWarehouse()->getName()]
            );

        $this->inventoryLevelSubscriber->afterNormalize($this->event);
    }

    public function getInventoryLevelEntity($quantity, $productUnitCode, $warehouse)
    {
        $object = new InventoryLevelStub();
        $object->setQuantity($quantity);

        $warehouse = new Warehouse();
        $warehouse->setName($warehouse);
        $object->setWarehouse($warehouse);

        return $object;
    }
}
