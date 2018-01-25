<?php
namespace Entrepids\Bundle\BraintreeBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class BraintreePaymentChannelType implements ChannelInterface, IconAwareIntegrationInterface
{

    const TYPE = 'braintree';

    /**
     * (non-PHPdoc)
     * @see \Oro\Bundle\IntegrationBundle\Provider\ChannelInterface::getLabel()
     */
    public function getLabel()
    {
        return 'entrepids.braintree.channel_type.braintree.label';
    }

    /**
     * (non-PHPdoc)
     * @see \Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface::getIcon()
     */
    public function getIcon()
    {
        return 'bundles/entrepidsbraintree/img/braintree-logo.png';
    }
}
