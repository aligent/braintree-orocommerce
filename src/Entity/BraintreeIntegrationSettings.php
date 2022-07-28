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

use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @package Aligent\BraintreeBundle\Entity
 * @ORM\Entity(repositoryClass="Aligent\BraintreeBundle\Entity\Repository\BraintreeIntegrationSettingsRepository")
 */
class BraintreeIntegrationSettings extends Transport
{
    /**
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="aligent_braintree_lbl",
     *      joinColumns={
     *          @ORM\JoinColumn(name="transport_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     * @var Collection<int,LocalizedFallbackValue>
     */
    protected Collection $labels;

    /**
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="aligent_braintree_sh_lbl",
     *      joinColumns={
     *          @ORM\JoinColumn(name="transport_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     * @var Collection<int,LocalizedFallbackValue>
     */
    protected Collection $shortLabels;

    /**
     * @var string
     * @ORM\Column(name="braintree_environment_type", type="string", length=255, nullable=false)
     */
    protected string $environment;

    /**
     * @var string
     * @ORM\Column(name="braintree_merch_id", type="string", length=255, nullable=false)
     */
    protected string $merchantId;

    /**
     * @var string
     * @ORM\Column(name="braintree_merch_account_id", type="string", length=255, nullable=false)
     */
    protected string $merchantAccountId;

    /**
     * @var string
     * @ORM\Column(name="braintree_merch_public_key", type="string", length=255, nullable=false)
     */
    protected string $publicKey;

    /**
     * @var string
     * @ORM\Column(name="braintree_merch_private_key", type="string", length=255, nullable=false)
     */
    protected string $privateKey;

    /**
     * @var boolean
     * @ORM\Column(name="braintree_vault", type="boolean", options={"default"=false})
     */
    protected bool $vault = false;

    /**
     * @var array<string,mixed>
     * @ORM\Column(name="braintree_settings", type="array")
     */
    protected array $paymentMethodSettings;

    /**
     * Column name shortened to braintree_fraud_advanced due to max db name length 30
     * @var boolean
     * @ORM\Column(name="braintree_fraud_advanced", type="boolean", options={"default"=false})
     */
    protected bool $fraudProtectionAdvanced = false;

    protected ?ParameterBag $settings = null;

    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->shortLabels = new ArrayCollection();
        $this->paymentMethodSettings = [
            'card' => [
                'enabled' => true,
            ]
        ];
    }

    /**
     * @return Collection<int, LocalizedFallbackValue>
     */
    public function getLabels(): Collection
    {
        return $this->labels;
    }

    public function addLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
        }

        return $this;
    }

    public function removeLabel(LocalizedFallbackValue $label): static
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }

    /**
     * @return Collection<int,LocalizedFallbackValue>
     */
    public function getShortLabels(): Collection
    {
        return $this->shortLabels;
    }

    public function addShortLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->shortLabels->contains($label)) {
            $this->shortLabels->add($label);
        }

        return $this;
    }

    public function removeShortLabel(LocalizedFallbackValue $label): static
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

    public function setEnvironment(string $environment): static
    {
        $this->environment = $environment;
        return $this;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId): static
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    public function getMerchantAccountId(): string
    {
        return $this->merchantAccountId;
    }

    public function setMerchantAccountId(string $merchantAccountId): static
    {
        $this->merchantAccountId = $merchantAccountId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): static
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function setPrivateKey(string $privateKey): static
    {
        $this->privateKey = $privateKey;
        return $this;
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

    public function setVaultMode(bool $vault): static
    {
        $this->vault = $vault;
        return $this;
    }

    public function getVaultMode(): bool
    {
        return $this->vault;
    }

    /**
     * @return array<string,mixed>
     */
    public function getPaymentMethodSettings(): array
    {
        return $this->paymentMethodSettings;
    }

    /**
     * @param array<string,mixed> $paymentMethodSettings
     */
    public function setPaymentMethodSettings(array $paymentMethodSettings): static
    {
        $this->paymentMethodSettings = $paymentMethodSettings;
        return $this;
    }

    public function isFraudProtectionAdvanced(): bool
    {
        return $this->fraudProtectionAdvanced;
    }

    public function setFraudProtectionAdvanced(bool $fraudProtectionAdvanced): static
    {
        $this->fraudProtectionAdvanced = $fraudProtectionAdvanced;
        return $this;
    }

    public function getSettingsBag(): ParameterBag
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    BraintreeConfigInterface::LABELS_KEY => $this->getLabels(),
                    BraintreeConfigInterface::SHORT_LABELS_KEY => $this->getShortLabels(),
                    BraintreeConfigInterface::ENVIRONMENT_KEY => $this->getEnvironment(),
                    BraintreeConfigInterface::MERCHANT_ACCOUNT_ID_KEY => $this->getMerchantAccountId(),
                    BraintreeConfigInterface::MERCHANT_ID_KEY => $this->getMerchantId(),
                    BraintreeConfigInterface::VAULT_KEY => $this->isVaultModeActive(),
                    BraintreeConfigInterface::FRAUD_PROTECTION_ADVANCED_KEY => $this->isFraudProtectionAdvanced()
                ]
            );
        }

        return $this->settings;
    }
}
