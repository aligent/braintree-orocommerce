<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;
use Entrepids\Bundle\BraintreeBundle\Integration\BraintreePaymentChannelType;
use Entrepids\Bundle\BraintreeBundle\Method\Config\Factory\BraintreeConfigFactoryInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Psr\Log\LoggerInterface;

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
     * @var string
     */
    protected $type;

    /**
     * @param ManagerRegistry                  $doctrine
     * @param LoggerInterface                  $logger
     * @param BraintreeConfigFactoryInterface $configFactory
     */
    public function __construct(
        ManagerRegistry $doctrine,
        LoggerInterface $logger,
        BraintreeConfigFactoryInterface $configFactory,
        $type
    ) {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->configFactory = $configFactory;
        $this->type = $type;
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
     * @return string
     */
    protected function getType()
    {
        return $this->type;
    }
    
    /**
     * @return BraintreeSettings[]
     */
    protected function getEnabledIntegrationSettings()
    {
        try {
            return $this->doctrine->getManagerForClass('EntrepidsBraintreeBundle:BraintreeSettings')
                ->getRepository('EntrepidsBraintreeBundle:BraintreeSettings')
                ->getEnabledSettingsByType($this->getType());
        } catch (\UnexpectedValueException $e) {
            $this->logger->critical($e->getMessage());

            return [];
        }
    }
}
