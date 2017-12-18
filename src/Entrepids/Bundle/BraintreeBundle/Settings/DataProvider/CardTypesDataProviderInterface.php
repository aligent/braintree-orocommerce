<?php

namespace Entrepids\Bundle\BraintreeBundle\Settings\DataProvider;

interface CardTypesDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getCardTypes();

    // ORO REVIEW:
    // This method violates Open/closed principle.
    // Environment is not related to card types.
    /**
     * @return string[]
     */
    public function getEnvironmentType();
}
