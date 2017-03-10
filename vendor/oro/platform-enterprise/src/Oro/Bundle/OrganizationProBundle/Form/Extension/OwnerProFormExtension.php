<?php

namespace Oro\Bundle\OrganizationProBundle\Form\Extension;

use Oro\Bundle\OrganizationBundle\Form\Extension\OwnerFormExtension;
use Oro\Bundle\OrganizationProBundle\Provider\SystemAccessModeOrganizationProvider;

class OwnerProFormExtension extends OwnerFormExtension
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
    protected function getOrganization()
    {
        // in case of System access mode, we should check if where is organization in org provider
        // and if it was set - use this organization
        $organization = $this->securityFacade->getOrganization();

        if ($organization->getIsGlobal() && $this->organizationProvider->getOrganizationId()) {
            $organization = $this->organizationProvider->getOrganization();
        }

        return $organization;
    }
}
