<?php

namespace Entrepids\Bundle\BraintreeBundle\Integration;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;
use Entrepids\Bundle\BraintreeBundle\Entity\Repository\BraintreeSettingsRepository;
use Entrepids\Bundle\BraintreeBundle\Form\Type\BraintreeSettingsType;
use Symfony\Component\HttpFoundation\ParameterBag;

class BraintreePaymentsTransport implements TransportInterface
{
	/** @var ParameterBag */
	protected $settings;

	/**
	 * @param Transport $transportEntity
	 */
	public function init(Transport $transportEntity)
	{
		$this->settings = $transportEntity->getSettingsBag();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSettingsFormType()
	{
		return BraintreeSettingsType::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSettingsEntityFQCN()
	{
		return BraintreeSettings::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLabel()
	{
		return 'entrepids.braintree.settings.label';
	}
}
