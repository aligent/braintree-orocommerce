<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\Config;

use Aligent\BraintreeBundle\Braintree\Gateway;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PaymentBundle\Method\Config\ParameterBag\AbstractParameterBagPaymentConfig;

class BraintreeConfig extends AbstractParameterBagPaymentConfig implements BraintreeConfigInterface
{
    public function getEnvironment(): string
    {
        return (string) $this->get(self::ENVIRONMENT_KEY);
    }

    public function getMerchantId(): string
    {
        return (string) $this->get(self::MERCHANT_ID_KEY);
    }

    public function getMerchantAccountId(): string
    {
        return (string) $this->get(self::MERCHANT_ACCOUNT_ID_KEY);
    }

    public function getPublicKey(): string
    {
        return (string) $this->get(self::PUBLIC_KEY_KEY);
    }

    public function getPrivateKey(): string
    {
        return (string) $this->get(self::PRIVATE_KEY_KEY);
    }

    public function setPublicKey(string $value): static
    {
        $this->set(self::PUBLIC_KEY_KEY, $value);
        return $this;
    }

    public function setPrivateKey(string $value): static
    {
        $this->set(self::PRIVATE_KEY_KEY, $value);
        return $this;
    }

    public function isVaultMode(): bool
    {
        return (boolean) $this->get(self::VAULT_KEY);
    }

    public function isSandboxMode(): bool
    {
        return $this->getEnvironment() === Gateway::SANDBOX;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabels(): ArrayCollection
    {
        return $this->get(self::LABELS_KEY);
    }

    /**
     * {@inheritDoc}
     */
    public function getShortLabels(): ArrayCollection
    {
        return $this->get(self::SHORT_LABELS_KEY);
    }

    public function getLabel(): string
    {
        return (string)$this->get(self::LABEL_KEY);
    }

    public function getShortLabel(): string
    {
        return (string)$this->get(self::SHORT_LABEL_KEY);
    }

    public function setLabel(string $value): static
    {
        $this->set(self::LABEL_KEY, $value);
        return $this;
    }

    public function setShortLabel(string $value): static
    {
        $this->set(self::LABEL_KEY, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethodSettings(): array
    {
        return $this->get(self::PAYMENT_METHODS_CONFIG_KEY);
    }

    public function setPaymentMethodSettings(array $settings): static
    {
        $this->set(self::PAYMENT_METHODS_CONFIG_KEY, $settings);
        return $this;
    }

    public function isFraudProtectionAdvancedEnabled(): bool
    {
        return (boolean) $this->get(self::FRAUD_PROTECTION_ADVANCED_KEY);
    }
}
