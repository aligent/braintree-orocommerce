<?php

namespace Oro\Bundle\WarehouseBundle\Provider;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ShippingBundle\Provider\ShippingOriginProvider;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;
use Oro\Bundle\WarehouseBundle\Entity\WarehouseAddress;

class WarehouseAddressProvider
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var null|ShippingOriginProvider */
    protected $shippingOriginProvider;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param ShippingOriginProvider $shippingOriginProvider
     */
    public function setShippingOriginProvider(ShippingOriginProvider $shippingOriginProvider)
    {
        $this->shippingOriginProvider = $shippingOriginProvider;
    }

    /**
     * @param Warehouse $warehouse
     * @return WarehouseAddress
     */
    public function getShippingOriginByWarehouse(Warehouse $warehouse)
    {
        $repo = $this->getWarehouseShippingOriginRepository();

        /** @var WarehouseAddress $shippingOriginWarehouse */
        $shippingOriginWarehouse = $repo->findOneBy(['warehouse' => $warehouse]);

        if (!empty($shippingOriginWarehouse)) {
            return $shippingOriginWarehouse;
        }

        $warehouseAddress = new WarehouseAddress();
        if (!empty($this->shippingOriginProvider)) {
            $warehouseAddress->import($this->shippingOriginProvider->getSystemShippingOrigin());
        }

        return $warehouseAddress;
    }

    /**
     * @return EntityRepository
     */
    protected function getWarehouseShippingOriginRepository()
    {
        return $this
            ->doctrineHelper
            ->getEntityManagerForClass(WarehouseAddress::class)
            ->getRepository(WarehouseAddress::class);
    }
}
