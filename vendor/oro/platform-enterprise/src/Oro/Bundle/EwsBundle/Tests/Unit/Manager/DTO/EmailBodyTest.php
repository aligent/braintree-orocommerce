<?php

namespace Oro\Bundle\EwsBundle\Tests\Unit\Manager\DTO;

use Oro\Bundle\EwsBundle\Manager\DTO\EmailBody;

class EmailBodyTest extends \PHPUnit_Framework_TestCase
{
    public function testGettersAndSetters()
    {
        $obj = new EmailBody();
        $obj
            ->setContent('testContent')
            ->setBodyIsText(true);
        $this->assertEquals('testContent', $obj->getContent());
        $this->assertTrue($obj->getBodyIsText());
    }
}
