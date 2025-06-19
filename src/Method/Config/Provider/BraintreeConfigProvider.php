<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\Config\Provider;

use Aligent\BraintreeBundle\Entity\BraintreeIntegrationSettings;
use Aligent\BraintreeBundle\Entity\Repository\BraintreeIntegrationSettingsRepository;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Aligent\BraintreeBundle\Method\Config\Factory\BraintreeConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class BraintreeConfigProvider implements BraintreeConfigProviderInterface
{
    protected ManagerRegistry $doctrine;
    protected LoggerInterface $logger;
    protected BraintreeConfigFactoryInterface $configFactory;

    /**
     * @var BraintreeConfigInterface[]
     */
    protected array $configs = [];

    public function __construct(
        ManagerRegistry $doctrine,
        LoggerInterface $logger,
        BraintreeConfigFactoryInterface $configFactory,
    ) {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->configFactory = $configFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentConfigs(): array
    {
        if (empty($this->configs)) {
            $settings = $this->getEnabledIntegrationSettings();

            foreach ($settings as $setting) {
                $config = $this->configFactory->create($setting);
                $this->configs[$config->getPaymentMethodIdentifier()] = $config;
            }
        }

        return $this->configs;
    }

    public function getPaymentConfig(string $identifier): ?BraintreeConfigInterface
    {
        $paymentConfigs = $this->getPaymentConfigs();

        if ([] === $paymentConfigs || false === array_key_exists($identifier, $paymentConfigs)) {
            return null;
        }

        return $paymentConfigs[$identifier];
    }

    public function hasPaymentConfig(string $identifier): bool
    {
        return null !== $this->getPaymentConfig($identifier);
    }

    /**
     * @return BraintreeIntegrationSettings[]
     */
    protected function getEnabledIntegrationSettings(): array
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
