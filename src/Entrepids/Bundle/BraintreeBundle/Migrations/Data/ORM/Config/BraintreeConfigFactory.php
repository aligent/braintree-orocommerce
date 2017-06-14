<?php

namespace Entrepids\Bundle\BraintreeBundle\Migrations\Data\ORM\Config;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class BraintreeConfigFactory
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return BraintreeConfig
     */
    public function createBraintreeConfig()
    {
        return new BraintreeConfig($this->configManager);
    }
}
