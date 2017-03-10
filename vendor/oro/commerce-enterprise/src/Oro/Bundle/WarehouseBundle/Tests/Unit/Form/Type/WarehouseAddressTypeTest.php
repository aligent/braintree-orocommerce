<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\WarehouseBundle\Entity\WarehouseAddress;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;
use Oro\Bundle\WarehouseBundle\Form\Type\WarehouseAddressType;
use Oro\Component\Testing\Unit\AddressFormExtensionTestCase;
use Oro\Component\Testing\Unit\Form\EventListener\Stub\AddressCountryAndRegionSubscriberStub;

class WarehouseAddressTypeTest extends AddressFormExtensionTestCase
{
    /** @var WarehouseAddressType */
    protected $formType;

    protected function setUp()
    {
        parent::setUp();

        $this->formType = new WarehouseAddressType(new AddressCountryAndRegionSubscriberStub());
        $this->formType->setDataClass(WarehouseAddress::class);
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals('oro_warehouse_address', $this->formType->getBlockPrefix());
    }

    /**
     * @param bool $isValid
     * @param array $submittedData
     * @param mixed $expectedData
     * @param mixed $defaultData
     * @param array $options
     *
     * @dataProvider submitProvider
     */
    public function testSubmit($isValid, $submittedData, $expectedData, $defaultData = null, $options = [])
    {
        $form = $this->factory->create($this->formType, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);

        $this->assertEquals($isValid, $form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function submitProvider()
    {
        return [
            'empty data' => [
                'isValid' => false,
                'submittedData' => [],
                'expectedData' => $this->getWarehouseAddress(),
                'defaultData' => $this->getWarehouseAddress(),
            ],
            'empty warehouse' => [
                'isValid' => false,
                'submittedData' => [
                    'country' => 'US',
                    'region' => 'US-AL',
                    'postalCode' => 'code1',
                    'city' => 'city1',
                    'street' => 'street1',
                ],
                'expectedData' => $this->getWarehouseAddress(true, 'warehouse'),
                'defaultData' => $this->getWarehouseAddress(false, 'warehouse'),
            ],
            'empty country' => [
                'isValid' => false,
                'submittedData' => [
                    'region' => 'US-AL',
                    'postalCode' => 'code1',
                    'city' => 'city1',
                    'street' => 'street1',
                ],
                'expectedData' => $this->getWarehouseAddress(true, 'country'),
                'defaultData' => $this->getWarehouseAddress(),
            ],
            'empty region' => [
                'isValid' => false,
                'submittedData' => [
                    'country' => 'US',
                    'postalCode' => 'code1',
                    'city' => 'city1',
                    'street' => 'street1',
                ],
                'expectedData' => $this->getWarehouseAddress(true, 'region'),
                'defaultData' => $this->getWarehouseAddress(),
            ],
            'empty postalCode' => [
                'isValid' => false,
                'submittedData' => [
                    'country' => 'US',
                    'region' => 'US-AL',
                    'city' => 'city1',
                    'street' => 'street1',
                ],
                'expectedData' => $this->getWarehouseAddress(true, 'postalCode'),
                'defaultData' => $this->getWarehouseAddress(),
            ],
            'empty city' => [
                'isValid' => false,
                'submittedData' => [
                    'country' => 'US',
                    'region' => 'US-AL',
                    'postalCode' => 'code1',
                    'street' => 'street1',
                ],
                'expectedData' => $this->getWarehouseAddress(true, 'city'),
                'defaultData' => $this->getWarehouseAddress(),
            ],
            'empty street' => [
                'isValid' => false,
                'submittedData' => [
                    'country' => 'US',
                    'region' => 'US-AL',
                    'postalCode' => 'code1',
                    'city' => 'city1',
                ],
                'expectedData' => $this->getWarehouseAddress(true, 'street'),
                'defaultData' => $this->getWarehouseAddress(),
            ],
            'full data' => [
                'isValid' => true,
                'submittedData' => [
                    'country' => 'US',
                    'region' => 'US-AL',
                    'postalCode' => 'code1',
                    'city' => 'city1',
                    'street' => 'street1',
                    'street2' => 'street2',
                ],
                'expectedData' => $this->getWarehouseAddress(true)->setStreet2('street2'),
                'defaultData' => $this->getWarehouseAddress(),
            ],
        ];
    }

    /**
     * @param bool $fill
     * @param string $exclude
     * @return WarehouseAddress
     */
    protected function getWarehouseAddress($fill = false, $exclude = '')
    {
        $warehouseAddress = new WarehouseAddress();

        if ($exclude !== 'warehouse') {
            $warehouseAddress->setWarehouse(new Warehouse());
        }

        if ($fill) {
            if ($exclude !== 'country') {
                $warehouseAddress->setCountry(new Country('US'));
            }

            if ($exclude !== 'region') {
                $region = new Region('US-AL');
                $region->setCountry(new Country('US'));

                $warehouseAddress->setRegion($region);
            }

            if ($exclude !== 'postalCode') {
                $warehouseAddress->setPostalCode('code1');
            }

            if ($exclude !== 'city') {
                $warehouseAddress->setCity('city1');
            }

            if ($exclude !== 'street') {
                $warehouseAddress->setStreet('street1');
            }
        }

        return $warehouseAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return array_merge(parent::getExtensions(), [$this->getValidatorExtension(true)]);
    }
}
