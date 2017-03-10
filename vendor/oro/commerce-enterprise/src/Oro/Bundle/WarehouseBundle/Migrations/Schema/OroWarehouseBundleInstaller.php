<?php

namespace Oro\Bundle\WarehouseBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroWarehouseBundleInstaller implements
    Installation,
    ExtendExtensionAwareInterface,
    ActivityExtensionAwareInterface
{
    const WAREHOUSE_TABLE_NAME = 'oro_warehouse';
    const INVENTORY_LEVEL = 'oro_inventory_level';

    const ORDER_TABLE_NAME = 'oro_order';
    const ORDER_LINE_ITEM_TABLE_NAME = 'oro_order_line_item';

    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**,
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroWarehouseTable($schema);
        $this->createOroWarehouseAddressTable($schema);

        /** Foreign keys generation **/
        $this->addOroWarehouseForeignKeys($schema);
        $this->addOroWarehouseAddressForeignKeys($schema);

        /** Extended fields **/
        $this->addWarehouseRelations($schema);

        $this->addWarehouseToInventoryLevel($schema);
    }

    /**
     * Create oro_warehouse table
     *
     * @param Schema $schema
     */
    protected function createOroWarehouseTable(Schema $schema)
    {
        $table = $schema->createTable(self::WAREHOUSE_TABLE_NAME);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('business_unit_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['created_at'], 'idx_oro_warehouse_created_at', []);
        $table->addIndex(['updated_at'], 'idx_oro_warehouse_updated_at', []);

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', $table->getName());
    }

    /**
     * Add oro_warehouse foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroWarehouseForeignKeys(Schema $schema)
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

    /**
     * Create oro_warehouse_address table
     *
     * @param Schema $schema
     */
    protected function createOroWarehouseAddressTable(Schema $schema)
    {
        $table = $schema->createTable('oro_warehouse_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('warehouse_id', 'integer', []);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created', 'datetime', []);
        $table->addColumn('updated', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['warehouse_id']);
    }

    /**
     * Add oro_warehouse_address foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroWarehouseAddressForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_warehouse_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add order related extended fields
     *
     * @param Schema $schema
     */
    protected function addWarehouseRelations(Schema $schema)
    {
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
            'name',
            [
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true,
                ],
                'entity' => ['label' => 'oro.warehouse.entity_label'],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_HIDDEN,
                    'show_filter' => true,
                ],
            ]
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $orderLineItemTable,
            'warehouse',
            $warehouseTable,
            'name',
            [
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true,
                ],
                'entity' => ['label' => 'oro.warehouse.entity_label'],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_HIDDEN,
                    'show_filter' => true,
                ]
            ]
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addWarehouseToInventoryLevel(Schema $schema)
    {
        $warehouseTable = $schema->getTable(self::WAREHOUSE_TABLE_NAME);
        $inventoryLevelTable = $schema->getTable(self::INVENTORY_LEVEL);

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $inventoryLevelTable,
            'warehouse',
            $warehouseTable,
            'name',
            [
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true
                ],
                'entity' => ['label' => 'oro.warehouse.entity_label'],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_TRUE,
                    'show_filter' => true,
                ]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getMigrationVersion()
    {
        return 'v1_4_1';
    }
}
