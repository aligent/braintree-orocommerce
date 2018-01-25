<?php

namespace Entrepids\Bundle\BraintreeBundle\Settings\DataProvider;

class BasicEnvironmentDataProvider implements EnvironmentDataProvider
{

    /**
     * @internal
     */
    const SANDBOX = 'sandbox';
    
    /**
     * @internal
     */
    const PRODUCTION = 'production';
    
    /**
     * @return string[]
     */
    public function getEnvironmentType()
    {
        return [
            self::SANDBOX,
            self::PRODUCTION,
        ];
    }
}
