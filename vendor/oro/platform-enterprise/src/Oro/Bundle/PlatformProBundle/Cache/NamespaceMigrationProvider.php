<?php

namespace Oro\Bundle\PlatformProBundle\Cache;

use Oro\Bundle\InstallerBundle\CacheWarmer\NamespaceMigrationProviderInterface;

class NamespaceMigrationProvider implements NamespaceMigrationProviderInterface
{
    /** @var string[] */
    protected $additionConfig
        = [
            'OroPro\Bundle\SecurityBundle\Migrations\Data\ORM\SetShareGridConfig' =>
                'Oro\Bundle\SecurityProBundle\Migrations\Data\ORM\SetShareGridConfig',
            'OroProOrganizationBundle'                                            => 'OroOrganizationProBundle',
            'OroProSecurityBundle'                                                => 'OroSecurityProBundle',
            'OroProUserBundle'                                                    => 'OroUserProBundle',
            'oropro_user_role_organization_select'                                =>
                'oro_userpro_role_organization_select',
            'oropro_organization_'                                                => 'oro_organizationpro_',
            'OroPro'                                                              => 'Oro',
        ];

    /**
     * (@inheritdoc}
     */
    public function getConfig()
    {
        return $this->additionConfig;
    }
}
