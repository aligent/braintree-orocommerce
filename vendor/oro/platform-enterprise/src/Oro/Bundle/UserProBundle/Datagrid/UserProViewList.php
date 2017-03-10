<?php

namespace Oro\Bundle\UserProBundle\Datagrid;

use Oro\Bundle\UserBundle\Datagrid\UserViewList;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserProBundle\Security\AuthStatus;

/**
 * Add locked status into filters for 'active' and 'cannot_login' grid views
 */
class UserProViewList extends UserViewList
{
    /**
     * {@inheritDoc}
     */
    protected function getViewsList()
    {
        foreach ($this->systemViews as $key => $item) {
            if ($item['name'] === 'user.cannot_login') {
                $this->systemViews[$key]['filters']['auth_status']['value'] = [
                    UserManager::STATUS_EXPIRED,
                    AuthStatus::LOCKED
                ];
            }
        }

        return $this->getSystemViewsList();
    }
}
