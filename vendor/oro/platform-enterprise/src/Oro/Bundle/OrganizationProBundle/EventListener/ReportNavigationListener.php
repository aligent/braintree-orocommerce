<?php

namespace Oro\Bundle\OrganizationProBundle\EventListener;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\ReportBundle\EventListener\NavigationListener;

use Oro\Bundle\OrganizationProBundle\Provider\SystemAccessModeOrganizationProvider;

class ReportNavigationListener extends NavigationListener
{
    /** @var SystemAccessModeOrganizationProvider */
    protected $organizationProvider;

    /**
     * @param SystemAccessModeOrganizationProvider $organizationProvider
     */
    public function setOrganizationProvider(SystemAccessModeOrganizationProvider $organizationProvider)
    {
        $this->organizationProvider = $organizationProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAvailability(ConfigInterface $config)
    {
        if (parent::checkAvailability($config)) {
            $applicable = $this->getOrganizationConfig($config)->get('applicable');
            return
                $applicable
                && (
                    $applicable['all'] == true
                    || in_array($this->securityFacade->getOrganizationId(), $applicable['selective'])
                );
        }

        return false;
    }

    /**
     * @param ConfigInterface $config
     *
     * @return ConfigInterface
     */
    protected function getOrganizationConfig(ConfigInterface $config)
    {
        $className                  = $config->getId()->getClassname();
        $configManager              = $this->entityConfigProvider->getConfigManager();
        $organizationConfigProvider = $configManager->getProvider('organization');

        return $organizationConfigProvider->getConfig($className);
    }
}
