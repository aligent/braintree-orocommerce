<?php

namespace Oro\Bundle\OrganizationProBundle\Tests\Unit\EventListener;

use Oro\Bundle\MigrationBundle\Event\PostMigrationEvent;
use Oro\Bundle\OrganizationProBundle\EventListener\PostUpMigrationListener;
use Oro\Bundle\OrganizationProBundle\Migrations\Schema\UpdateConfigsWithOrganizationMigration;

class PostUpMigrationListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testNotInstalled()
    {
        $listener = new PostUpMigrationListener(false);

        $event = $this->getMockBuilder(PostMigrationEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->never())->method($this->anything());

        $listener->onPostUp($event);
    }

    public function testInstalled()
    {
        $listener = new PostUpMigrationListener(true);

        $event = $this->getMockBuilder(PostMigrationEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())->method('addMigration')
            ->with($this->isInstanceOf(UpdateConfigsWithOrganizationMigration::class));

        $listener->onPostUp($event);
    }
}
