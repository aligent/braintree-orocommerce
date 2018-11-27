<?php
namespace Entrepids\Bundle\BraintreeBundle\Migrations\Schema\v1_1;

use Bianco\TenciaBundle\Entity\TenciaSpecialPrice;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddDisplayDetailsToTokenTable implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('braintree_customer_token');
        $table->addColumn('display_text', Type::STRING, [
            'default' => '',
            'notnull' => true,
        ]);
        $table->dropColumn('transaction');
    }
}
