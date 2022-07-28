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

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;

interface BraintreeConfigInterface extends PaymentConfigInterface
{
    const ENVIRONMENT_KEY = 'environment';
    const MERCHANT_ID_KEY = "merchant_id";
    const MERCHANT_ACCOUNT_ID_KEY = "merchant_account_id";
    const PUBLIC_KEY_KEY = "public_key";
    const PRIVATE_KEY_KEY = "private_key";
    const PAYMENT_METHODS_CONFIG_KEY = "payment_methods_config";
    const VAULT_KEY = 'vault';
    const LABELS_KEY = 'labels';
    const SHORT_LABELS_KEY = 'short_labels';
    const LABEL_KEY = 'label';
    const SHORT_LABEL_KEY = 'short_label';
    const FRAUD_PROTECTION_ADVANCED_KEY = 'fraud_protection_advanced';

    const PAYPAL_FLOW_VAULT = 'vault';
    const PAYPAL_FLOW_CHECKOUT = 'checkout';
    const PAYPAL_BILLING_PAGE = 'billing';
    const PAYPAL_LOGIN_PAGE = 'login';

    public function getEnvironment(): string;

    public function getMerchantId(): string;

    public function getMerchantAccountId(): string;

    public function getPublicKey(): string;

    public function setPublicKey(string $value): static;

    public function getPrivateKey(): string;

    public function setPrivateKey(string $value): static;

    public function isVaultMode(): bool;

    /**
     * Are we in sandbox mode?
     */
    public function isSandboxMode(): bool;

    public function isFraudProtectionAdvancedEnabled(): bool;

    /**
     * @return ArrayCollection<int,LocalizedFallbackValue>
     */
    public function getLabels(): ArrayCollection;

    /**
     * @return ArrayCollection<int,LocalizedFallbackValue>
     */
    public function getShortLabels(): ArrayCollection;

    public function getShortLabel(): string;

    public function setShortLabel(string $value): static;

    public function getLabel(): string;

    public function setLabel(string $value): static;

    /**
     * @return array<string,mixed>
     */
    public function getPaymentMethodSettings(): array;

    /**
     * @param array<string,mixed> $settings
     */
    public function setPaymentMethodSettings(array $settings): static;
}
