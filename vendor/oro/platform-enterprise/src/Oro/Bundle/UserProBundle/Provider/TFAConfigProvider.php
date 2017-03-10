<?php

namespace Oro\Bundle\UserProBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class TFAConfigProvider
{
    const CONFIG_STRENGTH_KEY         = 'oro_user_pro.two_factor_authentication_strength';
    const CONFIG_CODE_VALIDITY_PERIOD = 'oro_user_pro.authentication_code_validity_period';
    const CONFIG_CODE_LENGTH          = 'oro_user_pro.authentication_code_length';

    // AVAILABLE OPTIONS:
    const CONFIG_STRENGTH_DISABLED    = 'disabled';
    const CONFIG_STRENGTH_PER_DEVICE  = 'once_per_device';
    const CONFIG_STRENGTH_ALWAYS      = 'always_require';

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
     * @return string
     */
    public function getTFAStrength()
    {
        return (string) $this->configManager->get(self::CONFIG_STRENGTH_KEY);
    }

    /**
     * @return bool
     */
    public function isTFADisabled()
    {
        return $this->getTFAStrength() === self::CONFIG_STRENGTH_DISABLED;
    }

    /**
     * @return bool
     */
    public function isCodeRequiredAlways()
    {
        return $this->getTFAStrength() === self::CONFIG_STRENGTH_ALWAYS;
    }

    /**
     * @return bool
     */
    public function isCodeRequiredPerDevice()
    {
        return $this->getTFAStrength() === self::CONFIG_STRENGTH_PER_DEVICE;
    }

    /**
     * @return int
     */
    public function getCodeLength()
    {
        return (int) $this->configManager->get(self::CONFIG_CODE_LENGTH);
    }

    /**
     * @return \DateTime
     */
    public function getCodeExpiryDate()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        return $now->add($this->getCodeValidityPeriod());
    }

    /**
     * @return \DateInterval
     */
    protected function getCodeValidityPeriod()
    {
        $seconds = (int) $this->configManager->get(self::CONFIG_CODE_VALIDITY_PERIOD);

        return new \DateInterval('PT' . $seconds . 'S');
    }
}
