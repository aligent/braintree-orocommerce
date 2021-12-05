<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Jan Plank <jan.plank@aligent.com.au>
 * @copyright 2021 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddEnablePremiumFraudProtectionField implements Migration
{
    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');
        if (!$table->hasColumn('braintree_fraud_advanced')) {
            $table->addColumn('braintree_fraud_advanced', 'boolean', ['default' => '0', 'notnull' => false]);
        }
    }
}
