<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Stub;

use Oro\Bundle\MultiCurrencyBundle\Provider\ConfigDependencyInterface;

class StubConfigDependency implements ConfigDependencyInterface
{
    private $currencyList;

    /**
     * @inheritDoc
     */
    public function __construct($currencyList)
    {
        $this->currencyList = $currencyList;
    }

    /**
     * @inheritDoc
     */
    public function isValid($enabledCurrencyList, $organization)
    {
        $usedCurrencyList = array_diff($this->currencyList, $enabledCurrencyList);
        return empty($usedCurrencyList);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'stub';
    }
}
