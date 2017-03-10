<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Unit\Entity;

use Oro\Bundle\ShippingBundle\Model\ShippingOrigin;
use Oro\Bundle\WarehouseBundle\Entity\WarehouseAddress;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;

class WarehouseAddressTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;
    use EntityTrait;

    /** @var WarehouseAddress */
    protected $warehouseAddress;

    protected function setUp()
    {
        $this->warehouseAddress = new WarehouseAddress();
    }

    protected function tearDown()
    {
        unset($this->warehouseAddress);
    }

    public function testProperties()
    {
        $now = new \DateTime('now');

        $properties = [
            'id' => ['id', 1],
            'country' => ['country', $this->getEntity('Oro\Bundle\AddressBundle\Entity\Country')],
            'city' => ['city', 'city'],
            'postalCode' => ['postalCode', '12345'],
            'region' => ['region', $this->getEntity('Oro\Bundle\AddressBundle\Entity\Region')],
            'regionText' => ['regionText', 'test region'],
            'street' => ['street', 'street'],
            'street2' => ['street2', 'street2'],
            'created' => ['created', $now],
            'updated' => ['updated', $now],
            'warehouse' => ['warehouse', $this->getEntity('Oro\Bundle\WarehouseBundle\Entity\Warehouse')]
        ];

        $this->assertPropertyAccessors($this->warehouseAddress, $properties);
    }

    public function testIsSystem()
    {
        $this->assertFalse($this->warehouseAddress->isSystem());

        //form type mapping purpose
        $this->warehouseAddress->setSystem(true);
        $this->assertTrue($this->warehouseAddress->isSystem());
    }

    public function testPostLoad()
    {
        $this->assertAttributeEmpty('data', $this->warehouseAddress);

        $this->setProperty($this->warehouseAddress, 'country', 'test country');
        $this->setProperty($this->warehouseAddress, 'region', 'test region');
        $this->setProperty($this->warehouseAddress, 'regionText', 'test region_text');
        $this->setProperty($this->warehouseAddress, 'postalCode', 'test postalCode');
        $this->setProperty($this->warehouseAddress, 'city', 'test city');
        $this->setProperty($this->warehouseAddress, 'street', 'test street');
        $this->setProperty($this->warehouseAddress, 'street2', 'test street2');

        $this->warehouseAddress->postLoad();

        $this->assertAttributeEquals(
            new \ArrayObject(
                [
                    'country' => 'test country',
                    'region' => 'test region',
                    'region_text' => 'test region_text',
                    'postalCode' => 'test postalCode',
                    'city' => 'test city',
                    'street' => 'test street',
                    'street2' => 'test street2',
                    'system' => false,
                ]
            ),
            'data',
            $this->warehouseAddress
        );
    }

    public function testImport()
    {
        $this->assertTrue($this->warehouseAddress->isEmpty());

        $shippingOrigin = new ShippingOrigin(
            [
                'country' => 'test country',
                'region' => 'test region',
                'region_text' => 'test region_text',
                'postalCode' => 'test postalCode',
                'city' => 'test city',
                'street' => 'test street',
                'street2' => 'test street2',
                'system' => true,
            ]
        );

        $this->warehouseAddress->import($shippingOrigin);

        $this->assertAttributeEquals('test country', 'country', $this->warehouseAddress);
        $this->assertAttributeEquals('test region', 'region', $this->warehouseAddress);
        $this->assertAttributeEquals('test region_text', 'regionText', $this->warehouseAddress);
        $this->assertAttributeEquals('test postalCode', 'postalCode', $this->warehouseAddress);
        $this->assertAttributeEquals('test city', 'city', $this->warehouseAddress);
        $this->assertAttributeEquals('test street', 'street', $this->warehouseAddress);
        $this->assertAttributeEquals('test street2', 'street2', $this->warehouseAddress);
        $this->assertAttributeEquals(true, 'system', $this->warehouseAddress);
    }

    /**
     * @param object $object
     * @param string $property
     * @param mixed $value
     * @return $this
     */
    protected function setProperty($object, $property, $value)
    {
        $reflection = new \ReflectionProperty(get_class($object), $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);

        return $this;
    }
}
