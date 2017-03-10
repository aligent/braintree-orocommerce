<?php

namespace Oro\Bundle\SecurityCRMProBundle\Cache;

use Oro\Bundle\InstallerBundle\CacheWarmer\NamespaceMigrationProviderInterface;

class NamespaceMigrationProvider implements NamespaceMigrationProviderInterface
{
    /** @var string[] */
    protected $additionConfig
        = [
            'OroCRMPro\Bundle\SecurityBundle\Migrations\Data\ORM\SetShareGridConfig' =>
                'Oro\Bundle\SecurityCRMProBundle\Migrations\Data\ORM\SetShareGridConfig',
            'OroCRMPro'                                                              => 'Oro',
        ];

    /**
     * (@inheritdoc}
     */
    public function getConfig()
    {
        return $this->additionConfig;
    }
}
