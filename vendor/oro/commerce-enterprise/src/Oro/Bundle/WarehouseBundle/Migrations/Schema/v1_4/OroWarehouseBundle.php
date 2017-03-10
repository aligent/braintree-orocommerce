<?php

namespace Oro\Bundle\WarehouseBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroWarehouseBundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(
                'Oro\Bundle\InventoryBundle\Entity\InventoryLevel',
                'warehouse',
                'datagrid',
                'is_visible',
                DatagridScope::IS_VISIBLE_TRUE
            )
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(
                'Oro\Bundle\InventoryBundle\Entity\InventoryLevel',
                'warehouse',
                'datagrid',
                'show_filter',
                true
            )
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(
                'Oro\Bundle\InventoryBundle\Entity\InventoryLevel',
                'warehouse',
                'extend',
                'target_field',
                'name'
            )
        );
    }
}
