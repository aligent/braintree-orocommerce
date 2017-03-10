<?php

namespace Oro\Bundle\OrganizationProBundle\EventListener;

use Oro\Bundle\MigrationBundle\Event\PostMigrationEvent;
use Oro\Bundle\OrganizationProBundle\Migrations\Schema\UpdateConfigsWithOrganizationMigration;

class PostUpMigrationListener
{
    /** @var bool */
    private $installed;

    /**
     * @param bool $installed
     */
    public function __construct($installed)
    {
        $this->installed = (bool)$installed;
    }

    /**
     * @param PostMigrationEvent $event
     */
    public function onPostUp(PostMigrationEvent $event)
    {
        if (!$this->installed) {
            return;
        }

        $event->addMigration(new UpdateConfigsWithOrganizationMigration());
    }
}
