<?php

namespace Oro\Bundle\SecurityProBundle\Tokens;

use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\SecurityProBundle\Model\OrganizationTokenTrait;

class ProUsernamePasswordOrganizationToken extends UsernamePasswordOrganizationToken
{
    use OrganizationTokenTrait;

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $organization = $this->getOrganizationContext();
        $roles = parent::getRoles();

        $roles = $this->filterRolesInOrganizationContext($organization, $roles);

        return $roles;
    }
}
