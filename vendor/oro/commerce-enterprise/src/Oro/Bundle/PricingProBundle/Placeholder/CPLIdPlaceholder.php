<?php

namespace Oro\Bundle\PricingProBundle\Placeholder;

use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;

class CPLIdPlaceholder extends AbstractPlaceholder
{
    const NAME = 'CPL_ID';

    /**
     * {@inheritdoc}
     */
    public function getPlaceholder()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return '[0-9]+';
    }
}
