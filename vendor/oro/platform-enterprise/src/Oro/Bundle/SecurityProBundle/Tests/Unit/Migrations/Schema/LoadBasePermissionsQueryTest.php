<?php

namespace Oro\Bundle\SecurityProBundle\Tests\Unit\Migrations\Schema;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\SecurityBundle\Tests\Unit\Migrations\Schema\LoadBasePermissionsQueryTest as BaseTest;

use Oro\Bundle\SecurityProBundle\Migrations\Schema\LoadBasePermissionsQuery;

class LoadBasePermissionsQueryTest extends BaseTest
{
    public function testExecute()
    {
        $this->assertConnectionCalled(['SHARE'], 1);

        $query = new LoadBasePermissionsQuery();
        $query->setConnection($this->connection);
        $query->execute(new ArrayLogger());
    }
}
