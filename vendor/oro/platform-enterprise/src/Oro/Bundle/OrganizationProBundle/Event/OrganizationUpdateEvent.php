<?php

namespace Oro\Bundle\OrganizationProBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OrganizationUpdateEvent extends Event
{
    const NAME = 'oro_organizationpro.organization.update';

    /**
     * @var Organization
     */
    protected $organization;

    /**
     * @param Organization $organization
     */
    public function __construct(Organization $organization)
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
}
