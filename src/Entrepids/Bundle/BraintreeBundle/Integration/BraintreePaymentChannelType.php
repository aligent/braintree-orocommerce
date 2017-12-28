<?php
namespace Entrepids\Bundle\BraintreeBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class BraintreePaymentChannelType implements ChannelInterface, IconAwareIntegrationInterface
{

    const TYPE = 'braintree';

    /**
     *
     * @ERROR!!!
     *
     */
    public function getLabel()
    {
        return 'entrepids.braintree.channel_type.braintree.label';
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getIcon()
    {
        return 'bundles/braintree/img/braintree-logo.png';
    }
}
