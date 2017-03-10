<?php

namespace Oro\Bundle\SecurityProBundle\Tests\Unit\Event;

use Oro\Bundle\SecurityProBundle\Event\UpdateAcl;

class UpdateAclTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAcl()
    {
        $acl = $this->getMockBuilder('Symfony\Component\Security\Acl\Model\MutableAclInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $event = new UpdateAcl($acl);
        $this->assertEquals($acl, $event->getAcl());
    }
}
