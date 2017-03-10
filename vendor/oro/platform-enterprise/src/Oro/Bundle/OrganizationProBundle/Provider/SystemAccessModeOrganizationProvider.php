<?php

namespace Oro\Bundle\OrganizationProBundle\Provider;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * This class store organization for create/edit process in System access mode
 *
 * Class SystemAccessModeOrganizationProvider
 *
 * @package Oro\Bundle\OrganizationProBundle\Provider
 */
class SystemAccessModeOrganizationProvider
{
    /** @var Organization */
    protected $organization;

    /**
     * @param Organization $organization
     */
    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return bool|int
     */
    public function getOrganizationId()
    {
        if ($this->organization) {
            return $this->organization->getId();
        }

        return false;
    }
}
