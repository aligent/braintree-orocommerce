<?php

namespace Oro\Bundle\EwsBundle\Tests\Unit\Sync\Fixtures;

use Oro\Bundle\EwsBundle\Sync\EwsEmailSynchronizer;

class TestEwsEmailSynchronizer extends EwsEmailSynchronizer
{
    public function callCheckConfiguration()
    {
        return $this->checkConfiguration();
    }

    public function callCreateSynchronizationProcessor($origin)
    {
        return $this->createSynchronizationProcessor($origin);
    }

    public function callInitializeOrigins()
    {
        $this->initializeOrigins();
    }
}
