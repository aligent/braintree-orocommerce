<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\WarehouseBundle\Entity\Helper\WarehouseCounter;
use Oro\Bundle\WarehouseBundle\EventListener\OrderWarehouseGridListener;

class OrderWarehouseGridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WarehouseCounter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $warehouseCounter;

    /**
     * @var OrderWarehouseGridListener
     */
    protected $orderWarehouseGridListener;

    protected function setUp()
    {
        $this->warehouseCounter = $this->getMockBuilder(WarehouseCounter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderWarehouseGridListener = new OrderWarehouseGridListener($this->warehouseCounter);
    }

    public function testOnBuildBeforeShouldDoNothing()
    {
        /** @var BuildBefore|\PHPUnit_Framework_MockObject_MockObject $event * */
        $event = $this->getMockBuilder(BuildBefore::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->warehouseCounter->expects($this->once())
            ->method('areMoreWarehouses')
            ->willReturn(true);
        $event->expects($this->never())
            ->method('getConfig');

        $this->orderWarehouseGridListener->onBuildBefore($event);
    }

    public function testOnBuildBefore()
    {
        /** @var BuildBefore|\PHPUnit_Framework_MockObject_MockObject $event * */
        $event = $this->getMockBuilder(BuildBefore::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->warehouseCounter->expects($this->once())
            ->method('areMoreWarehouses')
            ->willReturn(false);

        $config = DatagridConfiguration::create([]);

        $event->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);
        $this->orderWarehouseGridListener->onBuildBefore($event);

        $this->assertEquals([
            'columns' => [
                'warehouse' => [
                    'renderable' => false,
                    'manageable' => false,
                ],
            ],
        ], $config->toArray());
    }
}
