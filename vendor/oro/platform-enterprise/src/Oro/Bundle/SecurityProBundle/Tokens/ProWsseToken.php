<?php

namespace Oro\Bundle\SecurityProBundle\Tokens;

use Oro\Bundle\UserBundle\Security\WsseToken;
use Oro\Bundle\SecurityProBundle\Model\OrganizationTokenTrait;

class ProWsseToken extends WsseToken
{
    use OrganizationTokenTrait;

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $organization = $this->getOrganizationContext();
        $roles = parent::getRoles();
        if (!$organization) {
            return $roles;
        }

        $roles = $this->filterRolesInOrganizationContext($organization, $roles);

        return $roles;
    }
}
