<?php

namespace Oro\Bundle\OrganizationProBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroProOrganizationBundle implements Migration
{
    /**
     * {@inheritdoc}
     *
     * @see \Oro\Bundle\OrganizationProBundle\Migrations\Schema\UpdateConfigsWithOrganizationMigration
     * @see \Oro\Bundle\OrganizationProBundle\Migrations\Schema\UpdateConfigsWithOrganizationQuery
     * @see \Oro\Bundle\OrganizationProBundle\EventListener\PostUpMigrationListener
     */
    public function up(Schema $schema, QueryBag $queries)
    {
    }
}
