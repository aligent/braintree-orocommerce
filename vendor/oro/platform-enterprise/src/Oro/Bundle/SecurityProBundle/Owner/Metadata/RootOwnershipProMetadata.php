<?php

namespace Oro\Bundle\SecurityProBundle\Owner\Metadata;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Owner\Metadata\RootOwnershipMetadata;

class RootOwnershipProMetadata extends RootOwnershipMetadata
{
    /**
     * {@inheritdoc}
     */
    public function getAccessLevelNames()
    {
        return AccessLevel::getAccessLevelNames(
            AccessLevel::BASIC_LEVEL,
            AccessLevel::SYSTEM_LEVEL
        );
    }
}
