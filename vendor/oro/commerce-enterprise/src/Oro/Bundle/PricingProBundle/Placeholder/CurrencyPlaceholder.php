<?php

namespace Oro\Bundle\PricingProBundle\Placeholder;

use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;

class CurrencyPlaceholder extends AbstractPlaceholder
{
    const NAME = 'CURRENCY';

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
