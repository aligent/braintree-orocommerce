<?php

namespace Oro\Bundle\LDAPBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\DefaultOwnerTypeAwareInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class ChannelType implements
    ChannelInterface,
    IconAwareIntegrationInterface,
    DefaultOwnerTypeAwareInterface
{
    const TYPE = 'ldap';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.ldap.integration.channel.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/oroldap/img/ldap_logo.png';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOwnerType()
    {
        return self::BUSINESS_UNIT;
    }
}
