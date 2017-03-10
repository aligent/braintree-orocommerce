<?php

namespace Oro\Bundle\SecurityProBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Handler\UserDeleteHandler as OroUserDeleteHandler;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

use Oro\Bundle\SecurityProBundle\Provider\ShareProvider;

class UserDeleteHandler extends OroUserDeleteHandler
{
    /** @var ShareProvider */
    protected $shareProvider;

    /**
     * @param ShareProvider $shareProvider
     */
    public function setShareProvider(ShareProvider $shareProvider)
    {
        $this->shareProvider = $shareProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        if ($this->shareProvider->hasUserSidSharedRecords($entity)) {
            throw new ForbiddenException('user has shared records');
        }

        parent::checkPermissions($entity, $em);
    }
}
