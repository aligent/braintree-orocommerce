<?php

namespace Entrepids\Bundle\BraintreeBundle\Migrations\Data\ORM\Config;

use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;

class BraintreeConfigToSettingsConverter
{
    /**
     * @param BraintreeConfig $config
     *
     * @return mixed
     */
    public function convert(BraintreeConfig $config)
    {
        $settings = new BraintreeSettings();

        $settings->addBraintreeLabel($config->getLabel())
            ->addBraintreeShortLabel($config->getShortLabel());

        return $settings;
    }
}
