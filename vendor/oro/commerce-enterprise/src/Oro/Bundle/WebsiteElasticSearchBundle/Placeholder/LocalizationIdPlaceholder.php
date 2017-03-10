<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Placeholder;

use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;

class LocalizationIdPlaceholder extends AbstractPlaceholder
{
    const NAME = 'LOCALIZATION_ID';

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return '([0-9]+|default)';
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaceholder()
    {
        return self::NAME;
    }
}
