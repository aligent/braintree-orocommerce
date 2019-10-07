<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 3:24 AM
 */

namespace Aligent\BraintreeBundle\Method\Config\Provider;


use Aligent\BraintreeBundle\Entity\BraintreeIntegrationSettings;
use Aligent\BraintreeBundle\Entity\Repository\BraintreeIntegrationSettingsRepository;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfig;
use Aligent\BraintreeBundle\Method\Config\Factory\BraintreeConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class BraintreeConfigProvider implements BraintreeConfigProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var BraintreeConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * @var BraintreeConfigInterface[]
     */
    protected $configs;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ManagerRegistry $doctrine
     * @param LoggerInterface $logger
     * @param BraintreeConfigFactoryInterface $configFactory
     */
    public function __construct(
        ManagerRegistry $doctrine,
        LoggerInterface $logger,
        BraintreeConfigFactoryInterface $configFactory
    ) {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->configFactory = $configFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentConfigs()
    {
        $configs = [];

        $settings = $this->getEnabledIntegrationSettings();

        foreach ($settings as $setting) {
            $config = $this->configFactory->create($setting);

            $configs[$config->getPaymentMethodIdentifier()] = $config;
        }

        return $configs;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentConfig($identifier)
    {
        $paymentConfigs = $this->getPaymentConfigs();

        if ([] === $paymentConfigs || false === array_key_exists($identifier, $paymentConfigs)) {
            return null;
        }

        return $paymentConfigs[$identifier];
    }

    /**
     * {@inheritDoc}
     */
    public function hasPaymentConfig($identifier)
    {
        return null !== $this->getPaymentConfig($identifier);
    }

    /**
     * @return BraintreeIntegrationSettings[]
     */
    protected function getEnabledIntegrationSettings()
    {
        try {
            /** @var BraintreeIntegrationSettingsRepository $repository */
            $repository = $this->doctrine
                ->getManagerForClass(BraintreeIntegrationSettings::class)
                ->getRepository(BraintreeIntegrationSettings::class);

            return $repository->getEnabledSettings();
        } catch (\UnexpectedValueException $e) {
            $this->logger->critical($e->getMessage());

            return [];
        }
    }
}