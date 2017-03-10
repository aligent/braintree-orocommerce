<?php

namespace Oro\Bundle\MultiCurrencyBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroConfigBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addRateTable($schema);
    }

    /**
     * Create oro_multicurrency_rate table
     *
     * @param Schema $schema
     */
    protected function addRateTable(Schema $schema)
    {
        $table = $schema->createTable('oro_multicurrency_rate');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn(
            'currency_code',
            'currency',
            ['length' => 3, 'notnull' => true, 'comment' => '(DC2Type:currency)']
        );
        $table->addColumn(
            'rate_from',
            'decimal',
            ['notnull' => true, 'precision' => 25, 'scale' => 10]
        );
        $table->addColumn(
            'rate_to',
            'decimal',
            ['notnull' => true, 'precision' => 25, 'scale' => 10]
        );
        $table->addColumn(
            'scope',
            'string',
            ['length' => 16, 'notnull' => true, 'comment' => '(DC2Type:string)']
        );
        $table->addIndex(['organization_id'], 'multicurrency_rate_organization_idx', []);
        $table->addIndex(['currency_code'], 'multicurrency_rate_currency_code_idx', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_5AB82CAE32C8A3DE'
        );
        $table->setPrimaryKey(['id']);
    }
}
