<?php

namespace Oro\Bundle\WarehouseBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\FrontendBundle\Migration\UpdateExtendRelationQuery;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RenameTablesAndColumns implements Migration, RenameExtensionAwareInterface
{
    /**
     * @var RenameExtension
     */
    private $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;

        // rename entities
        $extension->renameTable($schema, $queries, 'orob2b_warehouse', 'oro_warehouse');

        // rename indexes
        $schema->getTable('orob2b_warehouse')->dropIndex('idx_orob2b_warehouse_created_at');
        $schema->getTable('orob2b_warehouse')->dropIndex('idx_orob2b_warehouse_updated_at');

        $extension->addIndex($schema, $queries, 'oro_warehouse', ['created_at'], 'idx_oro_warehouse_created_at');
        $extension->addIndex($schema, $queries, 'oro_warehouse', ['updated_at'], 'idx_oro_warehouse_updated_at');
    }

    /**
     * {@inheritdoc}
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }
}
