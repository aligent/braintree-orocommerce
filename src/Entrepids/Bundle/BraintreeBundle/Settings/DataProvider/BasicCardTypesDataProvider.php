<?php

namespace Entrepids\Bundle\BraintreeBundle\Settings\DataProvider;

class BasicCardTypesDataProvider implements CardTypesDataProviderInterface
{
    /**
     * @internal
     */
    const VISA = 'visa';

    /**
     * @internal
     */
    const MASTERCARD = 'mastercard';

    /**
     * @internal
     */
    const DISCOVER = 'discover';

    /**
     * @internal
     */
    const AMERICAN_EXPRESS = 'american_express';

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
    public function getCardTypes()
    {
        return [
            self::VISA,
            self::MASTERCARD,
            self::DISCOVER,
            self::AMERICAN_EXPRESS,
        ];
    }

    /**
     * @return string[]
     */
    public function getEnvironmentType(){
    	return [
    		self::SANDBOX,
    		self::PRODUCTION,
    	];
    }
}
