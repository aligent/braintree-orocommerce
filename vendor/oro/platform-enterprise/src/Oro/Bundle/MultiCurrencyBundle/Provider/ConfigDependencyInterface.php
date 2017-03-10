<?php

namespace Oro\Bundle\MultiCurrencyBundle\Provider;

interface ConfigDependencyInterface
{
    /**
     * Returns is dependency valid or not
     *
     * @return bool
     */
    public function isValid($enabledCurrencyList, $organization);

    /**
     * Returns name of the dependency
     *
     * @return string
     */
    public function getName();
}
