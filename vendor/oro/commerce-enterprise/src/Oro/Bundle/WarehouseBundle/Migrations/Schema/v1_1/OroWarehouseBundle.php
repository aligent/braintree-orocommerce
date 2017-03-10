<?php

namespace Oro\Bundle\WarehouseBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroWarehouseBundle implements Migration, ExtendExtensionAwareInterface
{
    const WAREHOUSE_TABLE_NAME = 'orob2b_warehouse';
    const ORDER_TABLE_NAME = 'oro_order';
    const ORDER_LINE_ITEM_TABLE_NAME = 'oro_order_line_item';
    const INVENTORY_LEVEL_TABLE_NAME = 'oro_inventory_level';

    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if (!$schema->hasTable(self::WAREHOUSE_TABLE_NAME)) {
            $this->createOroWarehouseTable($schema);
            $this->addOroWarehouseForeignKeys($schema);
        }

        if (!$schema->hasTable(self::ORDER_TABLE_NAME) || !$schema->hasTable(self::ORDER_LINE_ITEM_TABLE_NAME)) {
            return;
        }

        $warehouseTable = $schema->getTable(self::WAREHOUSE_TABLE_NAME);
        $orderTable = $schema->getTable(self::ORDER_TABLE_NAME);
        $orderLineItemTable = $schema->getTable(self::ORDER_LINE_ITEM_TABLE_NAME);

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $orderTable,
            'warehouse',
            $warehouseTable,
            'id',
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM, 'without_default' => true]
            ]
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $orderLineItemTable,
            'warehouse',
            $warehouseTable,
            'id',
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM, 'without_default' => true]
            ]
        );
    }

    /**
     * Create orob2b_warehouse table
     *
     * @param Schema $schema
     */
    public function createOroWarehouseTable(Schema $schema)
    {
        $table = $schema->createTable(self::WAREHOUSE_TABLE_NAME);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('business_unit_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['created_at'], 'idx_orob2b_warehouse_created_at', []);
        $table->addIndex(['updated_at'], 'idx_orob2b_warehouse_updated_at', []);
    }

    /**
     * Add orob2b_warehouse foreign keys.
     *
     * @param Schema $schema
     */
    public function addOroWarehouseForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::WAREHOUSE_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_business_unit'),
            ['business_unit_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
