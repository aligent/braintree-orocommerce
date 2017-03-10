<?php

namespace Oro\Bundle\UserProBundle\Datagrid;

use Oro\Bundle\UserBundle\Datagrid\RolePermissionDatasource as Datasource;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Acl\Extension\ObjectIdentityHelper;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class RolePermissionDatasource extends Datasource
{
    /** @var  SecurityFacade */
    protected $securityFacade;

    /** @param SecurityFacade $securityFacade */
    public function setSecurityFacade(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $gridData = [];

        $currentOrganization = $this->securityFacade->getOrganization();
        if ($currentOrganization->getIsGlobal()) {
            $roleOrganization = $this->role->getOrganization();
            $organizationId = null !== $roleOrganization ? $roleOrganization->getId() : null;
        } else {
            $organizationId = $this->securityFacade->getOrganizationId();
        }

        $categories = $this->categoryProvider->getPermissionCategories();

        $allPrivileges = $this->preparePrivileges($this->role, 'entity');
        foreach ($allPrivileges as $privilege) {
            /** @var AclPrivilege $privilege */
            $identity = $privilege->getIdentity()->getId();

            if (null !== $organizationId && !$this->isPrivilegeAllowed($organizationId, $identity)) {
                continue;
            }

            $item = [
                'identity'    => $identity,
                'label'       => $privilege->getIdentity()->getName(),
                'group'       => $this->getPrivilegeCategory($privilege, $categories),
                'permissions' => []
            ];
            $fields = $this->getFieldPrivileges($privilege->getFields());
            if (count($fields)) {
                $item['fields'] = $fields;
            }
            $item = $this->preparePermissions($privilege, $item);
            $gridData[] = new ResultRecord($item);
        }
        return $gridData;
    }

    /**
     * Check entity restrictions by organization
     *
     * @param int    $organizationId
     * @param string $identity
     *
     * @return bool
     */
    protected function isPrivilegeAllowed($organizationId, $identity)
    {
        $className = substr($identity, strpos($identity, ObjectIdentityHelper::IDENTITY_TYPE_DELIMITER) + 1);
        $applicable = $this->configEntityManager
            ->getEntityConfig('organization', $className)
            ->get('applicable', false, false);

        return
            $applicable
            && (
                $applicable['all']
                || in_array($organizationId, $applicable['selective'])
            );
    }
}
