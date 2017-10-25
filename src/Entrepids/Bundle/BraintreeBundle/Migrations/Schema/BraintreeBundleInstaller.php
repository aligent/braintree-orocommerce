<?php

namespace Entrepids\Bundle\BraintreeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class BraintreeBundleInstaller implements Installation
{
    /**
     * {@inheritDoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_2';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOroIntegrationTransportTable($schema);
        $this->createBraintreeLblTable($schema);
        $this->createBraintreeShLblTable($schema);
        $this->addBraintreeLblForeignKeys($schema);
        $this->addBraintreeShLblForeignKeys($schema);
    }

    /**
     * Update oro_integration_transport table
     * @param Schema $schema
     */
    protected function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('braintree_payment_action', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('braintree_environment_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('braintree_allowed_card_types', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('braintree_auth_for_req_amount', 'boolean', ['default' => '0', 'notnull' => false]);
        $table->addColumn('braintree_merch_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('braintree_merch_account_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('braintree_merch_public_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('braintree_merch_private_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('braintree_safe_for_later', 'boolean', ['default' => '1', 'notnull' => false]);
        $table->addColumn('braintree_zero_amount', 'boolean', ['default' => '0', 'notnull' => false]);

    }

    /**
     * Create entrepids_braintree_lbl table
     * @param Schema $schema
     */
    protected function createBraintreeLblTable(Schema $schema)
    {
        $table = $schema->createTable('entrepids_braintree_lbl');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'UNIQ_92E5B87EEB576E89');
    }

    /**
     * Create entrepids_braintree_sh_lbl table
     * @param Schema $schema
     */
    protected function createBraintreeShLblTable(Schema $schema)
    {
        $table = $schema->createTable('entrepids_braintree_sh_lbl');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'UNIQ_55FE472FEB576E89');
    }

    /**
     * Add entrepids_braintree_lbl foreign keys.
     * @param Schema $schema
     */
    protected function addBraintreeLblForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('entrepids_braintree_lbl');
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
     * Add entrepids_braintree_sh_lbl foreign keys.
     * @param Schema $schema
     */
    protected function addBraintreeShLblForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('entrepids_braintree_sh_lbl');
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
