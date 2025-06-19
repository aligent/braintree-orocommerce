<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AligentBraintreeBundleInstaller implements Installation
{
    public function getMigrationVersion(): string
    {
        return 'v1_0';
    }

    /**
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        $this->createAligentBraintreeLblTable($schema);
        $this->createAligentBraintreeShLblTable($schema);
        $this->updateOroIntegrationTransportTable($schema);
        $this->extendCustomerUser($schema);

        /** Foreign keys generation **/
        $this->addAligentBraintreeLblForeignKeys($schema);
        $this->addAligentBraintreeShLblForeignKeys($schema);
    }

    protected function createAligentBraintreeLblTable(Schema $schema): void
    {
        $table = $schema->createTable('aligent_braintree_lbl');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'UNIQ_84655B45EB576E89');
        $table->addIndex(['transport_id'], 'IDX_84655B459909C13F', []);
    }

    protected function createAligentBraintreeShLblTable(Schema $schema): void
    {
        $table = $schema->createTable('aligent_braintree_sh_lbl');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'UNIQ_E52B6E47EB576E89');
        $table->addIndex(['transport_id'], 'IDX_E52B6E479909C13F', []);
    }

    /**
     * @throws SchemaException
     */
    protected function updateOroIntegrationTransportTable(Schema $schema): void
    {
        $table = $schema->getTable('oro_integration_transport');

        if (!$table->hasColumn('braintree_environment_type')) {
            $table->addColumn('braintree_environment_type', 'string', ['notnull' => false, 'length' => 255]);
        }

        if (!$table->hasColumn('braintree_merch_id')) {
            $table->addColumn('braintree_merch_id', 'string', ['notnull' => false, 'length' => 255]);
        }

        if (!$table->hasColumn('braintree_merch_account_id')) {
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
     * @throws SchemaException
     */
    protected function addAligentBraintreeLblForeignKeys(Schema $schema): void
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
     * @throws SchemaException
     */
    protected function addAligentBraintreeShLblForeignKeys(Schema $schema): void
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

    /**
     * @throws SchemaException
     */
    protected function extendCustomerUser(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user');

        $table->addColumn(
            'braintree_id',
            Types::STRING,
            [
                'notnull' => false,
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'form' => [
                        'is_enabled' => false,
                    ],
                    'view' => [
                        'is_displayable' => false,
                    ],
                    'dataaudit' => ['auditable' => true],
                ]
            ]
        );
    }
}
