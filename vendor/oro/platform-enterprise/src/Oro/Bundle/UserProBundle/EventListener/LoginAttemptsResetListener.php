<?php

namespace Oro\Bundle\UserProBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserProBundle\Security\AuthStatus;

/**
 * Responsible for resetting login failure counters
 */
class LoginAttemptsResetListener
{
    /**
     * Initialize failed login count to zero
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof User) {
            $entity->setFailedLoginCount(0);
        }
    }

    /**
     * Reset failed login count when user status is changed from 'locked'
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof User && $args->hasChangedField('auth_status')) {
            $oldValue = $args->getOldValue('auth_status');
            $oldStatusId = $oldValue ? $oldValue->getId() : null;
            if (AuthStatus::LOCKED === $oldStatusId) {
                $entity->setFailedLoginCount(0);
            }
        }
    }
}
