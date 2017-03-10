<?php

namespace Oro\Bundle\WarehouseBundle\Tests\Unit\ImportExport\Normalizer;

use Oro\Bundle\ImportExportBundle\Event\NormalizeEntityEvent;

class NormalizeEntityEventStub extends NormalizeEntityEvent
{
    public function getResult()
    {
        $result = parent::getResult();
        return array_merge($result, ['warehouse' => ['name' => 'testName']]);
    }
}
