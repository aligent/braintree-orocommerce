<?php

namespace Aligent\BraintreeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AligentBraintreeBundleInstaller implements Installation
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
        /** Tables generation **/
        $this->createAligentBraintreeLblTable($schema);
        $this->createAligentBraintreeShLblTable($schema);
        $this->updateOroIntegrationTransportTable($schema);

        /** Foreign keys generation **/
        $this->addAligentBraintreeLblForeignKeys($schema);
        $this->addAligentBraintreeShLblForeignKeys($schema);
    }

    /**
     * Create aligent_braintree_lbl table
     *
     * @param Schema $schema
     */
    protected function createAligentBraintreeLblTable(Schema $schema)
    {
        $table = $schema->createTable('aligent_braintree_lbl');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'UNIQ_84655B45EB576E89');
        $table->addIndex(['transport_id'], 'IDX_84655B459909C13F', []);
    }

    /**
     * Create aligent_braintree_sh_lbl table
     *
     * @param Schema $schema
     */
    protected function createAligentBraintreeShLblTable(Schema $schema)
    {
        $table = $schema->createTable('aligent_braintree_sh_lbl');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'UNIQ_E52B6E47EB576E89');
        $table->addIndex(['transport_id'], 'IDX_E52B6E479909C13F', []);
    }

    /**
     * Create oro_integration_transport table
     *
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');

        if (!$table->hasColumn('braintree_environment_type')) {
            $table->addColumn('braintree_environment_type', 'string', ['notnull' => false, 'length' => 255]);
        }

        if (!$table->hasColumn('braintree_merch_id')) {
            $table->addColumn('braintree_merch_id', 'string', ['notnull' => false, 'length' => 255]);
        }

        if (!$table->hasColumn('braintree_merch_account_id')){
            $table->addColumn('braintree_merch_account_id', 'string', ['notnull' => false, 'length' => 255]);
        }

        if (!$table->hasColumn('braintree_merch_public_key')) {
            $table->addColumn('braintree_merch_public_key', 'string', ['notnull' => false, 'length' => 255]);
        }

        if (!$table->hasColumn('braintree_merch_private_key')) {
            $table->addColumn('braintree_merch_private_key', 'string', ['notnull' => false, 'length' => 255]);
        }

        if (!$table->hasColumn('braintree_vault')) {
            $table->addColumn('braintree_vault', 'boolean', ['default' => '0', 'notnull' => false]);
        }

        if (!$table->hasColumn('braintree_settings')) {
            $table->addColumn('braintree_settings', 'array', ['notnull' => false]);
        }
    }

    /**
     * Add aligent_braintree_lbl foreign keys.
     *
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addAligentBraintreeLblForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('aligent_braintree_lbl');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add aligent_braintree_sh_lbl foreign keys.
     *
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addAligentBraintreeShLblForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('aligent_braintree_sh_lbl');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
