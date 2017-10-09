<?php

namespace Entrepids\Bundle\BraintreeBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class BraintreePaymentChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
	const TYPE = 'braintree';

	/**
	 * {@inheritdoc}
	 */
	public function getLabel()
	{
		return 'entrepids.braintree.channel_type.braintree.label';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIcon()
	{
		return 'bundles/braintree/img/braintree-logo.png';
	}
}
