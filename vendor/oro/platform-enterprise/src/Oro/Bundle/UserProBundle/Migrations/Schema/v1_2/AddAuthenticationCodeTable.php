<?php

namespace Oro\Bundle\UserProBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddAuthenticationCodeTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        static::createOroUserAuthenticationCodeTable($schema);
    }

    /**
     * Create oro_pro_user_auth_code table
     *
     * @param Schema $schema
     */
    public static function createOroUserAuthenticationCodeTable(Schema $schema)
    {
        $table = $schema->createTable('oro_pro_user_auth_code');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('code', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('expires_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addIndex(['code'], 'user_auth_code_idx', []);
    }
}
