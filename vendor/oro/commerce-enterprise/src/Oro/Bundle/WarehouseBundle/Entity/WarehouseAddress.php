<?php

namespace Oro\Bundle\WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\ShippingBundle\Model\ShippingOrigin;

/**
 * @ORM\Table("oro_warehouse_address")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class WarehouseAddress extends AbstractAddress
{
    /**
     * @var Warehouse
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WarehouseBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="warehouse_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $warehouse;

    /** @var bool */
    protected $system = false;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param Warehouse $warehouse
     *
     * @return $this
     */
    public function setWarehouse(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @return boolean
     */
    public function isSystem()
    {
        return $this->system;
    }

    /**
     * @param boolean $system
     * @return WarehouseAddress
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * @ORM\PostLoad()
     */
    public function postLoad()
    {
        $this->data = new \ArrayObject(
            [
                'country' => $this->country,
                'region' => $this->region,
                'region_text' => $this->regionText,
                'postalCode' => $this->postalCode,
                'city' => $this->city,
                'street' => $this->street,
                'street2' => $this->street2,
                'system' => $this->system,
            ]
        );
    }

    /**
     * @param ShippingOrigin $shippingOrigin
     * @return $this
     */
    public function import(ShippingOrigin $shippingOrigin)
    {
        $this->setCountry($shippingOrigin->getCountry())
            ->setSystem($shippingOrigin->isSystem())
            ->setRegion($shippingOrigin->getRegion())
            ->setRegionText($shippingOrigin->getRegionText())
            ->setPostalCode($shippingOrigin->getPostalCode())
            ->setCity($shippingOrigin->getCity())
            ->setStreet($shippingOrigin->getStreet())
            ->setStreet2($shippingOrigin->getStreet2());

        return $this;
    }

    /**
     * @param AbstractAddress $address
     * @return $this
     */
    public function importAddress(AbstractAddress $address)
    {
        $this->setCountry($address->getCountry())
            ->setRegion($address->getRegion())
            ->setRegionText($address->getRegionText())
            ->setPostalCode($address->getPostalCode())
            ->setCity($address->getCity())
            ->setStreet($address->getStreet())
            ->setStreet2($address->getStreet2());

        return $this;
    }
}
