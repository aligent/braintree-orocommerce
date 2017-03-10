<?php

namespace Oro\Bundle\SecurityProBundle\Acl\Persistence;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnitInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\BaseAclManager as OroAclManager;

use Oro\Bundle\SecurityProBundle\Acl\Domain\BusinessUnitSecurityIdentity;
use Oro\Bundle\SecurityProBundle\Acl\Domain\OrganizationSecurityIdentity;

class BaseAclManager extends OroAclManager implements AclSidInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSid($identity)
    {
        try {
            return parent::getSid($identity);
        } catch (\InvalidArgumentException $e) {
            if ($identity instanceof BusinessUnitInterface) {
                return BusinessUnitSecurityIdentity::fromBusinessUnit($identity);
            } elseif ($identity instanceof OrganizationInterface) {
                return OrganizationSecurityIdentity::fromOrganization($identity);
            }

            throw new \InvalidArgumentException(
                sprintf(
                    '$identity must be a string or implement one of RoleInterface, UserInterface, TokenInterface,'
                    . ' BusinessUnitInterface, OrganizationInterface (%s given)',
                    is_object($identity) ? get_class($identity) : gettype($identity)
                )
            );
        }
    }
}
