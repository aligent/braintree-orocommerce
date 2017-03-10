<?php

namespace Oro\Bundle\WarehouseBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroWarehouseBundle implements Migration, ExtendExtensionAwareInterface, RenameExtensionAwareInterface
{
    const WAREHOUSE_TABLE_NAME = 'oro_warehouse';
    const INVENTORY_LEVEL = 'oro_inventory_level';
    const SHIPING_ORIGIN_WAREHOUSE = 'oro_shipping_orig_warehouse';
    const WAREHOUSE_ADDRESS = 'oro_warehouse_address';

    /** @var ExtendExtension */
    protected $extendExtension;

    /** @var  RenameExtension */
    protected $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * Sets the RenameExtension
     *
     * @param RenameExtension $renameExtension
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->resolveShippingOriginWarehouseTable($schema, $queries);
        $this->addWarehouseToInventoryLevel($schema);

        $options = [
            'extend' => [
                'is_extend' => true,
                'owner' => ExtendScope::OWNER_CUSTOM,
                'without_default' => true,
                'target_field' => 'name',
            ],
            'entity' => ['label' => 'oro.warehouse.entity_label'],
            'datagrid' => [
                'is_visible' => DatagridScope::IS_VISIBLE_HIDDEN,
                'show_filter' => true,
            ],
        ];

        $orderTable = $schema->getTable('oro_order');
        $this->dropWarehouseFromOrder($orderTable);

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $orderTable,
            'warehouse',
            $schema->getTable('oro_warehouse'),
            'name',
            $options
        );

        $orderLineItemTable = $schema->getTable('oro_order_line_item');
        $this->dropWarehouseFromOrderLineItem($orderLineItemTable);
        $this->extendExtension->addManyToOneRelation(
            $schema,
            $orderLineItemTable,
            'warehouse',
            $schema->getTable('oro_warehouse'),
            'name',
            $options
        );
    }

    /**
     * @param Table $orderTable
     */
    protected function dropWarehouseFromOrder(Table $orderTable)
    {
        if ($orderTable->hasForeignKey('fk_oro_order_warehouse_id')) {
            $orderTable->removeForeignKey('fk_oro_order_warehouse_id');
        }
        if ($orderTable->hasForeignKey('fk_orob2b_order_warehouse_id')) {
            $orderTable->removeForeignKey('fk_orob2b_order_warehouse_id');
        }
        if ($orderTable->hasIndex('idx_oro_order_warehouse_id')) {
            $orderTable->dropIndex('idx_oro_order_warehouse_id');
        }
        if ($orderTable->hasIndex('idx_orob2b_order_warehouse_id')) {
            $orderTable->dropIndex('idx_orob2b_order_warehouse_id');
        }
        $orderTable->dropColumn('warehouse_id');
    }

    /**
     * @param Table $orderLineItemTable
     */
    protected function dropWarehouseFromOrderLineItem(Table $orderLineItemTable)
    {
        if ($orderLineItemTable->hasForeignKey('fk_32715f0b5080ecde')) {
            $orderLineItemTable->removeForeignKey('fk_32715f0b5080ecde');
        }
        if ($orderLineItemTable->hasForeignKey('IDX_DE9136095080ECDE')) {
            $orderLineItemTable->removeForeignKey('IDX_DE9136095080ECDE');
        }

        if ($orderLineItemTable->hasIndex('idx_32715f0b5080ecde')) {
            $orderLineItemTable->dropIndex('idx_32715f0b5080ecde');
        }
        if ($orderLineItemTable->hasIndex('IDX_DE9136095080ECDE')) {
            $orderLineItemTable->dropIndex('IDX_DE9136095080ECDE');
        }
        $orderLineItemTable->dropColumn('warehouse_id');
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function resolveShippingOriginWarehouseTable(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable(self::SHIPING_ORIGIN_WAREHOUSE)) {
            $this->renameExtension->renameTable(
                $schema,
                $queries,
                self::SHIPING_ORIGIN_WAREHOUSE,
                self::WAREHOUSE_ADDRESS
            );

            return;
        }

        $this->createOroWarehouseAddressTable($schema);
        $this->addOroWarehouseAddressForeignKeys($schema);
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
                    'without_default' => true,
                    'cascade' => ['remove'],
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
}
