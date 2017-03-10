<?php

namespace Oro\Bundle\SecurityProBundle\Acl\Extension;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationContextTokenInterface;

class EntityAclProExtension extends EntityAclExtension
{
    /**
     * {@inheritdoc}
     */
    public function decideIsGranting($triggeredMask, $object, TokenInterface $securityToken)
    {
        // check if we are in global mode - return false in case if Access Level < AccessLevel::SYSTEM_LEVEL
        if ($securityToken instanceof OrganizationContextTokenInterface) {
            $organization = $securityToken->getOrganizationContext();

            if ($organization->getIsGlobal() && $this->getAccessLevel($triggeredMask) !== AccessLevel::SYSTEM_LEVEL) {
                return false;
            }
        }

        return parent::decideIsGranting($triggeredMask, $object, $securityToken);
    }

    /**
     * {@inheritdoc}
     */
    protected function isAccessDeniedByOrganizationContext($object, OrganizationContextTokenInterface $securityToken)
    {
        if ($securityToken->getOrganizationContext()->getIsGlobal()) {
            return false;
        }

        return parent::isAccessDeniedByOrganizationContext($object, $securityToken);
    }

    /**
     * {@inheritdoc}
     */
    protected function getOwnershipPermissions()
    {
        return array_merge(parent::getOwnershipPermissions(), ['SHARE']);
    }
}
