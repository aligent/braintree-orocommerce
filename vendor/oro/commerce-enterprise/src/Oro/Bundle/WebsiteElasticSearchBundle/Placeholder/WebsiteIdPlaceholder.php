<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Placeholder;

use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;

class WebsiteIdPlaceholder extends AbstractPlaceholder
{
    const NAME = 'WEBSITE_ID';

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return '[0-9]+';
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaceholder()
    {
        return self::NAME;
    }
}
