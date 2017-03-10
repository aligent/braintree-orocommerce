<?php

namespace Oro\Bundle\PricingProBundle\Placeholder;

use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;

class UnitPlaceholder extends AbstractPlaceholder
{
    const NAME = 'UNIT';

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
        return '\w+';
    }
}
