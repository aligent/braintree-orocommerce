<?php

namespace Oro\Bundle\UserProBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class PasswordChangePeriodConfigProvider
{
    /** @var ConfigManager */
    protected $configManager;

    const PASSWORD_EXPIRY_ENABLED = 'oro_user_pro.password_change_period_enabled';
    const PASSWORD_EXPIRY_PERIOD = 'oro_user_pro.password_change_period';
    const PASSWORD_EXPIRY_NOTIFICATION_DAYS = 'oro_user_pro.password_change_notification_days';

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return bool
     */
    public function isPasswordChangePeriodEnabled()
    {
        return (bool) $this->configManager->get(self::PASSWORD_EXPIRY_ENABLED);
    }

    /**
     * @return array
     */
    public function getNotificationDays()
    {
        return (array) $this->configManager->get(self::PASSWORD_EXPIRY_NOTIFICATION_DAYS);
    }

    /**
     * @return int
     */
    public function getExpiryPeriod()
    {
        return (int) $this->configManager->get(self::PASSWORD_EXPIRY_PERIOD);
    }

    /**
     * @return \DateTime|null
     */
    public function getPasswordExpiryDateFromNow()
    {
        if (!$this->isPasswordChangePeriodEnabled()) {
            return null;
        }

        return new \DateTime(sprintf("+%d days", $this->getExpiryPeriod()), new \DateTimeZone('UTC'));
    }
}
