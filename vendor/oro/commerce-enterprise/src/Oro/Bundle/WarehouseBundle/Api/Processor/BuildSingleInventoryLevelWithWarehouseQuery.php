<?php

namespace Oro\Bundle\WarehouseBundle\Api\Processor;

use Doctrine\DBAL\Query\QueryBuilder;

use Oro\Bundle\ApiBundle\Processor\Update\UpdateContext;
use Oro\Bundle\ApiBundle\Util\CriteriaConnector;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\InventoryBundle\Api\Processor\BuildSingleInventoryLevelQuery;
use Oro\Bundle\WarehouseBundle\Entity\Helper\WarehouseCounter;
use Oro\Component\ChainProcessor\ContextInterface;

class BuildSingleInventoryLevelWithWarehouseQuery extends BuildSingleInventoryLevelQuery
{
    /** @var  WarehouseCounter */
    protected $warehouseCounter;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param CriteriaConnector $criteriaConnector
     * @param WarehouseCounter $warehouseCounter
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        CriteriaConnector $criteriaConnector,
        WarehouseCounter $warehouseCounter
    ) {
        parent::__construct($doctrineHelper, $criteriaConnector);

        $this->warehouseCounter = $warehouseCounter;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        if (!$context instanceof UpdateContext) {
            return;
        }

        if ($context->hasQuery()) {
            // a query is already built
            return;
        }

        $initialRequestData = $context->getRequestData();
        parent::process($context);
        if (!$context->hasQuery()) {
            return;
        }
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $context->getQuery();

        $requestData = $context->getRequestData();
        if ($this->warehouseCounter->areMoreWarehouses()) {
            if (!array_key_exists('warehouse', $requestData)) {
                // warehouse is required if there are more warehouses in the system
                $context->setQuery(null);
                $context->setRequestData($initialRequestData);

                return;
            }
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('e.warehouse', ':warehouse'))
                ->setParameter('warehouse', $requestData['warehouse']);
            unset($requestData['warehouse']);
        }

        $context->setQuery($queryBuilder);
        $context->setRequestData($requestData);
    }
}
