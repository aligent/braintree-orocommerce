<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Fixture\Entity;

use Oro\Bundle\OrganizationBundle\Entity\Organization as ParentOrganization;

class Organization extends ParentOrganization
{
    /** @var  null|int */
    protected $is_global;

    public function getIsGlobal()
    {
        return $this->is_global;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setIsGlobal($value)
    {
        $this->is_global = $value;
        return $this;
    }
}
