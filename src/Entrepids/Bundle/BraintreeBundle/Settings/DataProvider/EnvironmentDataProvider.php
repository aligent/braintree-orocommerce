<?php

namespace Entrepids\Bundle\BraintreeBundle\Settings\DataProvider;

interface EnvironmentDataProvider
{

    /**
     * @return string[]
     */
    public function getEnvironmentType();
}
