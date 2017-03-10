<?php

namespace Oro\Bundle\WarehouseBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Request\JsonApi\JsonApiDocumentBuilder as JsonApiDoc;
use Oro\Bundle\ApiBundle\Processor\FormContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\InventoryBundle\Api\Processor\NormalizeInventoryLevelRequestData;
use Oro\Bundle\WarehouseBundle\Entity\Helper\WarehouseCounter;
use Oro\Bundle\WarehouseBundle\Entity\Repository\WarehouseRepository;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;
use Oro\Component\ChainProcessor\ContextInterface;

class NormalizeInventoryLevelWithWarehouseRequestData extends NormalizeInventoryLevelRequestData
{
    const WAREHOUSE = 'warehouse';

    /** @var  WarehouseCounter */
    protected $warehouseCounter;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param WarehouseCounter $warehouseCounter
     */
    public function __construct(DoctrineHelper $doctrineHelper, WarehouseCounter $warehouseCounter)
    {
        parent::__construct($doctrineHelper);
        $this->warehouseCounter = $warehouseCounter;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        if (!$context instanceof FormContext) {
            return;
        }

        $initialRequestData = $context->getRequestData();
        if (!$initialRequestData) {
            return;
        }

        parent::process($context);
        $requestData = $context->getRequestData();
        if ($initialRequestData === $requestData) {
            return;
        }

        $relationships = $requestData[JsonApiDoc::DATA][JsonApiDoc::RELATIONSHIPS];
        if ($this->warehouseCounter->areMoreWarehouses()) {
            if (!$this->isRelationshipValid($relationships, self::WAREHOUSE)) {
                // warehouse is required if there are more warehouses in the system
                $context->setRequestData($initialRequestData);

                return;
            }
        } else {
            $warehouse = $this->resolveWarehouse();
            if ($warehouse) {
                $this->addRelationship($relationships, self::WAREHOUSE, Warehouse::class, $warehouse->getId());
            }
        }

        $requestData[JsonApiDoc::DATA][JsonApiDoc::RELATIONSHIPS] = $relationships;
        $context->setRequestData($requestData);
    }

    /**
     * @return null|Warehouse
     */
    protected function resolveWarehouse()
    {
        /** @var WarehouseRepository $warehouseRepository */
        $warehouseRepository = $this->doctrineHelper->getEntityRepository(Warehouse::class);

        return $warehouseRepository->getSingularWarehouse();
    }
}
