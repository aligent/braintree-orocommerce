<?php

namespace Entrepids\Bundle\BraintreeBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Entrepids\Bundle\BraintreeBundle\Integration\BraintreePaymentChannelType;
use Entrepids\Bundle\BraintreeBundle\Migrations\Data\ORM\Config\ChannelFactory;
use Entrepids\Bundle\BraintreeBundle\Migrations\Data\ORM\Config\BraintreeConfigFactory;
use Entrepids\Bundle\BraintreeBundle\Migrations\Data\ORM\Config\BraintreeConfigToSettingsConverter;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\PaymentBundle\Migrations\Data\ORM\AbstractMoveConfigValuesToSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MoveConfigValuesToSettings extends AbstractMoveConfigValuesToSettings
{
	const SECTION_NAME = 'braintree';

	/**
	 * @var ChannelFactory
	 */
	protected $channelFactory;

	/**
	 * @var BraintreeConfigFactory
	 */
	protected $configFactory;

	/**
	 * @var BraintreeConfigToSettingsConverter
	 */
	protected $configToSettingsConverter;

	/**
	 * @var IntegrationIdentifierGeneratorInterface
	 */
	protected $methodIdentifierGenerator;

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		parent::setContainer($container);

		$this->methodIdentifierGenerator = $container->get('entrepids_braintree.generator.braintree_config_identifier');
		$this->channelFactory = $this->createChannelFactory($container);
		$this->configFactory = $this->createConfigFactory($container);
		$this->configToSettingsConverter = new BraintreeConfigToSettingsConverter();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function moveConfigFromSystemConfigToIntegration(
			ObjectManager $manager,
			OrganizationInterface $organization
	) {
		$braintreeSystemConfig = $this->configFactory->createBraintreeConfig();

		$channel = $this->channelFactory->createChannel(
				$organization,
				$this->configToSettingsConverter->convert($braintreeSystemConfig),
				$braintreeSystemConfig->isAllRequiredFieldsSet()
		);

		$manager->persist($channel);
		$manager->flush();

		$this->dispatchPaymentMethodRenamingEvent($channel);

		$manager->flush();
	}

	/**
	 * @param Channel $channel
	 */
	protected function dispatchPaymentMethodRenamingEvent(Channel $channel)
	{
		$this->dispatcher->dispatch(
				BraintreePaymentChannelType::TYPE,
				$this->methodIdentifierGenerator->generateIdentifier($channel)
		);
	}

	/**
	 * @param ContainerInterface $container
	 *
	 * @return ChannelFactory
	 */
	protected function createChannelFactory(ContainerInterface $container)
	{
		return new ChannelFactory(
				$container->get('entrepids_braintree.integration.channel'),
				$container->get('translator')
		);
	}

	/**
	 * @param ContainerInterface $container
	 *
	 * @return MoneyOrderConfigFactory
	 */
	protected function createConfigFactory(ContainerInterface $container)
	{
		return new BraintreeConfigFactory(
				$container->get('oro_config.manager')
		);
	}
}
