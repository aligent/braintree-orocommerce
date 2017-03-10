<?php

namespace Oro\Bundle\SecurityProBundle\Tokens;

use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationRememberMeToken;
use Oro\Bundle\SecurityProBundle\Model\OrganizationTokenTrait;

class ProOrganizationRememberMeToken extends OrganizationRememberMeToken
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
