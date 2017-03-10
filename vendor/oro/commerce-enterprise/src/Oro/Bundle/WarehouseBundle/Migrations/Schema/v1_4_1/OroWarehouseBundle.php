<?php

namespace Oro\Bundle\WarehouseBundle\Migrations\Schema\v1_4_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigCascadeQuery;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;

class OroWarehouseBundle implements Migration
{
    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // removing cascade deletion of warehouses in config and config-field
        $queries->addQuery(
            new UpdateEntityConfigFieldValueQuery(
                InventoryLevel::class,
                'warehouse',
                'extend',
                'cascade',
                []
            )
        );
        $queries->addPostQuery(new UpdateEntityConfigCascadeQuery(
            InventoryLevel::class,
            Warehouse::class,
            RelationType::MANY_TO_ONE,
            'warehouse',
            []
        ));
    }
}
