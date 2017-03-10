<?php

namespace Oro\Bundle\SecurityProBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\SecurityProBundle\Migrations\Schema\v1_0\OroProSecurityBundle as OroProSecurityBundle10;

class OroSecurityProBundleInstaller implements Installation
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
        OroProSecurityBundle10::updateAclTables($schema);

        $queries->addPostQuery(new LoadBasePermissionsQuery());
    }
}
