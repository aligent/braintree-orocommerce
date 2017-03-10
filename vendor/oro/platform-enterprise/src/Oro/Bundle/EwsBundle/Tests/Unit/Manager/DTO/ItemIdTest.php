<?php

namespace Oro\Bundle\EwsBundle\Tests\Unit\Manager\DTO;

use Oro\Bundle\EwsBundle\Manager\DTO\ItemId;

class ItemIdTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $obj = new ItemId('testId', 'testChangeKey');
        $this->assertEquals('testId', $obj->getId());
        $this->assertEquals('testChangeKey', $obj->getChangeKey());
    }

    public function testGettersAndSetters()
    {
        $obj = new ItemId('test', 'test');
        $obj
            ->setId('testId')
            ->setChangeKey('testChangeKey');
        $this->assertEquals('testId', $obj->getId());
        $this->assertEquals('testChangeKey', $obj->getChangeKey());
    }
}
