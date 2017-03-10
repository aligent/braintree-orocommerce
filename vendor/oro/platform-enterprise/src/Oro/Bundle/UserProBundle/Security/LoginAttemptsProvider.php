<?php

namespace Oro\Bundle\UserProBundle\Security;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\UserBundle\Entity\User;

class LoginAttemptsProvider
{
    const LIMIT_ENABLED = 'oro_user_pro.failed_login_limit_enabled';
    const LIMIT         = 'oro_user_pro.failed_login_limit';

    /** @var  ConfigManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param  User $user
     *
     * @return int
     */
    public function getRemaining(User $user)
    {
        return max(0, $this->getLimit() - (int)$user->getFailedLoginCount());
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return (int)$this->configManager->get(self::LIMIT);
    }

    /**
     * @return bool
     */
    public function hasLimit()
    {
        return (bool)$this->configManager->get(self::LIMIT_ENABLED);
    }

    /**
     * @param  User $user
     *
     * @return bool
     */
    public function hasReachedLimit(User $user)
    {
        return $this->hasLimit() && 0 === $this->getRemaining($user);
    }
}
