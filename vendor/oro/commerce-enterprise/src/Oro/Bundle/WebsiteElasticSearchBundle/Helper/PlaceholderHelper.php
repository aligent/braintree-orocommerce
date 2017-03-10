<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Helper;

use Oro\Bundle\WebsiteSearchBundle\Placeholder\PlaceholderRegistry;

class PlaceholderHelper
{
    /** @var PlaceholderRegistry */
    private $placeholderRegistry;

    /**
     * @param PlaceholderRegistry $placeholderRegistry
     */
    public function __construct(PlaceholderRegistry $placeholderRegistry)
    {
        $this->placeholderRegistry = $placeholderRegistry;
    }

    /**
     * @param string $alias
     * @param string $aliasValue
     * @return bool
     */
    public function isAliasMatch($alias, $aliasValue)
    {
        $placeholderNames = [];
        $placeholderPatterns = [];
        foreach ($this->placeholderRegistry->getPlaceholders() as $placeholder) {
            $placeholderNames[] = $placeholder->getPlaceholder();
            $placeholderPatterns[] = $placeholder->getDefaultValue();
        }

        $aliasPattern = str_replace($placeholderNames, $placeholderPatterns, $alias);

        return preg_match('/' . $aliasPattern . '/', $aliasValue);
    }
}
