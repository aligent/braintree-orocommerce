<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 2:25 AM
 */

namespace Aligent\BraintreeBundle\Integration;


use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class BraintreeChannelType implements ChannelInterface, IconAwareIntegrationInterface
{

    /**
     * Returns label for UI
     *
     * @return string
     */
    public function getLabel()
    {
        return 'aligent.braintree.channel_type.label';
    }

    /**
     * Returns icon path for UI, should return value like 'bundles/acmedemo/img/logo.png'
     * Relative path to assets helper
     *
     * @return string
     */
    public function getIcon()
    {
        return 'bundles/aligentbraintree/img/braintree-logo.png';
    }
}