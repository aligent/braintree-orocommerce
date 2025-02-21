<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Entity;

use Aligent\BraintreeBundle\Entity\Repository\BraintreeIntegrationSettingsRepository;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class BraintreeIntegrationSettings
 * @package Aligent\BraintreeBundle\Entity
 */
#[ORM\Entity(repositoryClass: BraintreeIntegrationSettingsRepository::class)]
class BraintreeIntegrationSettings extends Transport
{
    /**
     *
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(
        name: 'aligent_braintree_lbl',
        joinColumns: [
            new ORM\JoinColumn(
                name: 'transport_id',
                referencedColumnName: 'id',
                onDelete: 'CASCADE'
            )
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(
                name: 'localized_value_id',
                referencedColumnName: 'id',
                unique: true,
                onDelete: 'CASCADE'
            )
        ]
    )]
    protected $labels;

    /**
     *
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(
        name: 'aligent_braintree_sh_lbl',
        joinColumns: [
            new ORM\JoinColumn(
                name: 'transport_id',
                referencedColumnName: 'id',
                onDelete: 'CASCADE'
            )
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(
                name: 'localized_value_id',
                referencedColumnName: 'id',
                unique: true,
                onDelete: 'CASCADE'
            )
        ]
    )]
    protected $shortLabels;

    #[ORM\Column(name: 'braintree_environment_type', type: 'string', length: 255, nullable: false)]
    protected string $environment;

    #[ORM\Column(name: 'braintree_merch_id', type: 'string', length: 255, nullable: false)]
    protected string $merchantId;

    #[ORM\Column(name: 'braintree_merch_account_id', type: 'string', length: 255, nullable: false)]
    protected string $merchantAccountId;

    #[ORM\Column(name: 'braintree_merch_public_key', type: 'string', length: 255, nullable: false)]
    protected string $publicKey;

    #[ORM\Column(name: 'braintree_merch_private_key', type: 'string', length: 255, nullable: false)]
    protected string $privateKey;

    #[ORM\Column(name: 'braintree_vault', type: 'boolean', options: ['default' => false])]
    protected bool $vault = false;

    #[ORM\Column(name: 'braintree_settings', type: 'array')]
    protected array $paymentMethodSettings;

    protected ParameterBag $settings;

    /**
     * Column name shortened to braintree_fraud_advanced due to max db name length 30
     */
    #[ORM\Column(name: 'braintree_fraud_advanced', type: 'boolean', options: ['default' => false])]
    protected bool $fraudProtectionAdvanced = false;

    /**
     * BraintreeIntegrationSettings constructor.
     */
    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->shortLabels = new ArrayCollection();
        $this->paymentMethodSettings = [
            'card' => [
                'enabled' => true
            ]
        ];
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getLabels(): array|ArrayCollection|Collection
    {
        return $this->labels;
    }

    /**
     * @param LocalizedFallbackValue $label
     *
     * @return $this
     */
    public function addLabel(LocalizedFallbackValue $label)
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $label
     *
     * @return $this
     */
    public function removeLabel(LocalizedFallbackValue $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getShortLabels(): array|ArrayCollection|Collection
    {
        return $this->shortLabels;
    }

    /**
     * @param LocalizedFallbackValue $label
     *
     * @return $this
     */
    public function addShortLabel(LocalizedFallbackValue $label)
    {
        if (!$this->shortLabels->contains($label)) {
            $this->shortLabels->add($label);
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $label
     *
     * @return $this
     */
    public function removeShortLabel(LocalizedFallbackValue $label)
    {
        if ($this->shortLabels->contains($label)) {
            $this->shortLabels->removeElement($label);
        }

        return $this;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function setMerchantId($merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    public function getMerchantAccountId(): string
    {
        return $this->merchantAccountId;
    }

    public function setMerchantAccountId(string $merchantAccountId): void
    {
        $this->merchantAccountId = $merchantAccountId;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function setPrivateKey(string $privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    public function isVaultModeActive(): bool
    {
        return $this->vault;
    }

    public function enableVaultMode(): void
    {
        $this->vault = true;
    }

    public function disableVaultMode(): void
    {
        $this->vault = false;
    }

    public function setVaultMode(bool $vault): void
    {
        $this->vault = $vault;
    }

    public function getVaultMode(): bool
    {
        return $this->vault;
    }

    public function getPaymentMethodSettings(): array
    {
        return $this->paymentMethodSettings;
    }

    public function setPaymentMethodSettings(array $paymentMethodSettings): void
    {
        $this->paymentMethodSettings = $paymentMethodSettings;
    }

    public function isFraudProtectionAdvanced(): bool
    {
        return $this->fraudProtectionAdvanced;
    }

    public function setFraudProtectionAdvanced(bool $fraudProtectionAdvanced): void
    {
        $this->fraudProtectionAdvanced = $fraudProtectionAdvanced;
    }

    /**
     * @return ParameterBag
     */
    public function getSettingsBag(): ParameterBag
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    BraintreeConfig::LABELS_KEY => $this->getLabels(),
                    BraintreeConfig::SHORT_LABELS_KEY => $this->getShortLabels(),
                    BraintreeConfig::ENVIRONMENT_KEY => $this->getEnvironment(),
                    BraintreeConfig::MERCHANT_ACCOUNT_ID_KEY => $this->getMerchantAccountId(),
                    BraintreeConfig::MERCHANT_ID_KEY => $this->getMerchantId(),
                    BraintreeConfig::VAULT_KEY => $this->isVaultModeActive(),
                    BraintreeConfig::FRAUD_PROTECTION_ADVANCED_KEY => $this->isFraudProtectionAdvanced()
                ]
            );
        }

        return $this->settings;
    }
}
