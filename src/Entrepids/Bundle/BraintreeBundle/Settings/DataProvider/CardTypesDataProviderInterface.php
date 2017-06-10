<?php

namespace Entrepids\Bundle\BraintreeBundle\Settings\DataProvider;

interface CardTypesDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getCardTypes();
    
    public function getEnvironmentType();
}
