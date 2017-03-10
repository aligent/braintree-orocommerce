<?php

namespace Oro\Bundle\UserProBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\UserProBundle\Provider\PasswordChangePeriodConfigProvider;

class PasswordExpiryPeriodChangeListener
{
    /** @var Registry */
    protected $registry;

    /** @var PasswordChangePeriodConfigProvider */
    protected $provider;

    /**
     * @param Registry $registry
     * @param PasswordChangePeriodConfigProvider $provider
     */
    public function __construct(Registry $registry, PasswordChangePeriodConfigProvider $provider)
    {
        $this->registry = $registry;
        $this->provider = $provider;
    }

    /**
     * @param ConfigUpdateEvent $event
     */
    public function onConfigUpdate(ConfigUpdateEvent $event)
    {
        $isSettingActive = $this->provider->isPasswordChangePeriodEnabled();
        $isSettingToggled = $event->isChanged(PasswordChangePeriodConfigProvider::PASSWORD_EXPIRY_ENABLED);

        // do not update password expiration after setting was switched from active to inactive
        if (!$isSettingActive && !$isSettingToggled) {
            return;
        }

        if ($isSettingToggled || $event->isChanged(PasswordChangePeriodConfigProvider::PASSWORD_EXPIRY_PERIOD)) {
            $this->resetPasswordExpiryDates();
        }
    }

    /**
     * Update passwordExpiresAt of all users with a new date
     */
    protected function resetPasswordExpiryDates()
    {
        $newExpiryDate = $this->provider->getPasswordExpiryDateFromNow();

        $qb = $this->registry->getRepository('OroUserBundle:User')
            ->createQueryBuilder('u')
            ->update()
            ->set('u.password_expires_at', ':expiryDate')
            ->setParameter('expiryDate', $newExpiryDate, Type::DATETIME);

        $qb->getQuery()->execute();
    }
}
