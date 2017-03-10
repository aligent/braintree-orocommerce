<?php

namespace Oro\Bundle\OrganizationProBundle\Autocomplete;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Autocomplete\BusinessUnitTreeSearchHandler as BaseBusinessUnitTreeSearchHandler;

use Oro\Bundle\OrganizationProBundle\Helper\OrganizationProHelper;

class BusinessUnitTreeSearchHandler extends BaseBusinessUnitTreeSearchHandler
{
    /** @var OrganizationProHelper */
    protected $organizationProHelper;

    /**
     * @param OrganizationProHelper $organizationProHelper
     */
    public function setOrganizationProHelper(OrganizationProHelper $organizationProHelper)
    {
        $this->organizationProHelper = $organizationProHelper;
    }

    /**
     * @param BusinessUnit $businessUnit
     * @param $path
     *
     * @return mixed
     */
    protected function getPath($businessUnit, $path)
    {
        array_unshift($path, ['name'=> $businessUnit->getName()]);

        $owner = $businessUnit->getOwner();
        if ($owner) {
            $path = $this->getPath($owner, $path);
        } else {
            $organization = $this->securityFacade->getOrganization();
            if (($organization && $organization->getIsGlobal()) ||
                !$this->organizationProHelper->isGlobalOrganizationExists()
            ) {
                array_unshift($path, ['name'=> $businessUnit->getOrganization()->getName()]);
            }
        }
        
        return $path;
    }
}
