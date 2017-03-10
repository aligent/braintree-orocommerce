<?php

namespace Oro\Bundle\MultiWebsiteBundle\Matcher;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\PhpUtils\ArrayUtil;

class WebsiteMatcherRegistry
{
    /**
     * @var array|WebsiteMatcherInterface[]
     */
    protected $matchers = [];

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return array|WebsiteMatcherInterface[]
     */
    public function getRegisteredMatchers()
    {
        return $this->matchers;
    }

    /**
     * @return array|WebsiteMatcherInterface[]
     */
    public function getEnabledMatchers()
    {
        $matchersConfig = $this->configManager->get('oro_multiwebsite.website_matchers_settings');
        $matchers = $this->getRegisteredMatchers();
        ArrayUtil::sortBy($matchers, true, 'priority');
        if ($matchersConfig) {
            $matchersConfig = array_filter(
                $matchersConfig,
                function (array $matcherConfig) {
                    return !empty($matcherConfig['enabled']);
                }
            );
            ArrayUtil::sortBy($matchersConfig, true, 'priority');
            $sortedAliases = ArrayUtil::arrayColumn($matchersConfig, 'matcher_alias');
            $result = [];
            foreach ($sortedAliases as $alias) {
                if (array_key_exists($alias, $matchers)) {
                    $result[$alias] = $matchers[$alias];
                }
            }
            
            return $result;
        }
        
        return $matchers;
    }

    /**
     * @param string $alias
     * @param WebsiteMatcherInterface $matcher
     */
    public function addMatcher($alias, WebsiteMatcherInterface $matcher)
    {
        $this->matchers[$alias] = $matcher;
    }
}
