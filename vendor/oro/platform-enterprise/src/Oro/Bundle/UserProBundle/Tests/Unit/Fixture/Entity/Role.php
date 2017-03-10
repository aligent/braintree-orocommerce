<?php
namespace Oro\Bundle\UserProBundle\Tests\Unit\Fixture\Entity;

use Oro\Bundle\UserBundle\Entity\Role as ParentRole;
use Oro\Bundle\OrganizationBundle\Entity\Organization as RoleOrganization;

class Role extends ParentRole
{
    /** @var  null|RoleOrganization */
    protected $organization;

    /**
     * @param null|RoleOrganization $value
     * @return $this
     */
    public function setOrganization($value)
    {
        $this->organization = $value;
        return $this;
    }

    /**
     * @return \Extend\Entity\EX_OroOrganizationBundle_Organization|RoleOrganization|null
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}
