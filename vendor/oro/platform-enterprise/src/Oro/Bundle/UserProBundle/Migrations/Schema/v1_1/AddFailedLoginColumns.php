<?php

namespace Oro\Bundle\UserProBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;

use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddFailedLoginColumns implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateOroUserTable($schema);
    }

    /**
     * Add failed_login_count to User
     *
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public static function updateOroUserTable(Schema $schema)
    {
        $table = $schema->getTable('oro_user');
        $table->addColumn(
            'failed_login_count',
            'integer',
            [
                OroOptions::KEY => [
                    'extend'                          => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                    'form'                            => ['is_enabled' => false],
                    'datagrid'                        => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'importexport'                    => ['excluded' => true],
                    ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                ],
            ]
        );
    }
}
