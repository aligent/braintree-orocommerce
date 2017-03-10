<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Stub;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class ConfigManagerStub extends ConfigManager
{
    public function __construct()
    {
    }

    /**
     * @var int
     */
    protected $scopeId;

    /**
     * @var array
     */
    protected $configs;

    public function setConfigs(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * @param string $name
     * @param bool   $default
     * @param bool   $full
     * @param null   $scopeIdentifier
     *
     * @return mixed
     */
    public function get($name, $default = false, $full = false, $scopeIdentifier = null)
    {
        return $this->configs[$this->scopeId];
    }

    /**
     * @param int $scopeId
     */
    public function setScopeId($scopeId)
    {
        $this->scopeId = $scopeId;
    }

    /**
     * @return int
     */
    public function getScopeId()
    {
        return $this->scopeId;
    }
}
