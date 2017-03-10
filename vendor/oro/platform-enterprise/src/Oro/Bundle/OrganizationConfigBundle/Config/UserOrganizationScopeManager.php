<?php

namespace Oro\Bundle\OrganizationConfigBundle\Config;

use Oro\Bundle\ConfigBundle\Config\UserScopeManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationContextTokenInterface;
use Oro\Bundle\UserBundle\Entity\User;

class UserOrganizationScopeManager extends UserScopeManager
{
    /**
     * {@inheritdoc}
     */
    public function getScopedEntityName()
    {
        return 'organization_user';
    }

    /**
     * {@inheritdoc}
     */
    protected function isSupportedScopeEntity($entity)
    {
        return $entity instanceof User;
    }

    /**
     * @param User $entity
     *
     * {@inheritdoc}
     */
    protected function getScopeEntityIdValue($entity)
    {
        return $this->findScopeId($entity);
    }

    /**
     * @return string
     */
    public function getScopeInfo()
    {
        return 'oropro.user_configuration.scope_tooltip';
    }

    /**
     * {@inheritdoc}
     */
    protected function ensureScopeIdInitialized()
    {
        if (null === $this->scopeId) {
            $this->scopeId = $this->findScopeId();
        }
    }

    /**
     * @param User|null $user
     * @return int
     */
    protected function findScopeId($user = null)
    {
        $scopeId = 0;
        $token = $this->securityContext->getToken();
        if ($token instanceof OrganizationContextTokenInterface) {
            $user = $user ?: $token->getUser();
            if ($user instanceof User && $user->getId()) {
                $organization = $token->getOrganizationContext();
                if ($organization instanceof Organization && $organization->getId()) {
                    $scopeId = $this->getUserOrganizationId($user, $organization);
                }
            }
        }

        return $scopeId;
    }

    /**
     * @param User         $user
     * @param Organization $organization
     *
     * @return int
     */
    protected function getUserOrganizationId(User $user, Organization $organization)
    {
        return $this->doctrine->getManagerForClass('OroOrganizationProBundle:UserOrganization')
            ->getRepository('OroOrganizationProBundle:UserOrganization')
            ->getUserOrganization($user, $organization)
            ->getId();
    }
}
