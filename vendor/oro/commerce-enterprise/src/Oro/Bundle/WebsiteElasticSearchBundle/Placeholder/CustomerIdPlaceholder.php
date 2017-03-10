<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Placeholder;

use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;

class CustomerIdPlaceholder extends AbstractPlaceholder
{
    const NAME = 'ACCOUNT_ID';

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
