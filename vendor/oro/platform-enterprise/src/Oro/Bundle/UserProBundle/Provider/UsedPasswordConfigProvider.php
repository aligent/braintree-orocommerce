<?php

namespace Oro\Bundle\UserProBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class UsedPasswordConfigProvider
{
    const USED_PASSWORD_CHECK_ENABLED = 'oro_user_pro.used_password_check_enabled';
    const USED_PASSWORD_CHECK_NUMBER = 'oro_user_pro.used_password_check_number';

    /** @var ConfigManager */
    protected $configManager;

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
    public function isUsedPasswordCheckEnabled()
    {
        return (bool) $this->configManager->get(self::USED_PASSWORD_CHECK_ENABLED);
    }

    /**
     * @return int
     */
    public function getUsedPasswordsCheckNumber()
    {
        return (int) $this->configManager->get(self::USED_PASSWORD_CHECK_NUMBER);
    }
}
