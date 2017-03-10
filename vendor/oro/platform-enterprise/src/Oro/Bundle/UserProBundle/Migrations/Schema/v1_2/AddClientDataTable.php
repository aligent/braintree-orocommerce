<?php

namespace Oro\Bundle\UserProBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddClientDataTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        static::createOroUserClientDataTable($schema);
    }

    /**
     * Create oro_pro_user_client_data table
     *
     * @param Schema $schema
     */
    public static function createOroUserClientDataTable(Schema $schema)
    {
        $table = $schema->createTable('oro_pro_user_client_data');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('ip_address', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('user_agent', 'string', ['notnull' => true, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addIndex(['user_id', 'ip_address', 'user_agent'], 'user_client_composite_idx', []);
    }
}
