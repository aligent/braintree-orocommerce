<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 12:39 AM
 */

namespace Aligent\BraintreeBundle\Entity;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class BraintreeIntegrationSettings
 * @package Aligent\BraintreeBundle\Entity
 * @ORM\Entity(repositoryClass="Aligent\BraintreeBundle\Entity\Repository\BraintreeIntegrationSettingsRepository")
 */
class BraintreeIntegrationSettings extends Transport
{
    /**
     *
     * @var Collection|LocalizedFallbackValue[] @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     *      )
     * @ORM\JoinTable(
     *      name="aligent_braintree_lbl",
     *      joinColumns={
     *      @ORM\JoinColumn(name="transport_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *      @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     *      )
     */
    protected $labels;

    /**
     *
     * @var Collection|LocalizedFallbackValue[] @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     *      )
     * @ORM\JoinTable(
     *      name="aligent_braintree_sh_lbl",
     *      joinColumns={
     *      @ORM\JoinColumn(name="transport_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *      @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     *      )
     */
    protected $shortLabels;

    /**
     *
     * @var string @ORM\Column(name="braintree_environment_type", type="string", length=255, nullable=false)
     */
    protected $environment;

    /**
     *
     * @var string @ORM\Column(name="braintree_merch_id", type="string", length=255, nullable=false)
     */
    protected $merchantId;

    /**
     *
     * @var string @ORM\Column(name="braintree_merch_account_id", type="string", length=255, nullable=false)
     */
    protected $merchantAccountId;

    /**
     *
     * @var string @ORM\Column(name="braintree_merch_public_key", type="string", length=255, nullable=false)
     */
    protected $publicKey;

    /**
     *
     * @var string @ORM\Column(name="braintree_merch_private_key", type="string", length=255, nullable=false)
     */
    protected $privateKey;

    /**
     *
     * @var boolean @ORM\Column(name="braintree_vault", type="boolean", options={"default"=false})
     */
    protected $vault = false;

    /**
     *
     * @var array @ORM\Column(name="braintree_settings", type="array")
     */
    protected $paymentMethodSettings;

    /**
     * @var ParameterBag
     */
    protected $settings;

    /**
     * BraintreeIntegrationSettings constructor.
     */
    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->shortLabels = new ArrayCollection();
        $this->paymentMethodSettings = [];
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getLabels()
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
    public function getShortLabels()
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

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getMerchantAccountId()
    {
        return $this->merchantAccountId;
    }

    /**
     * @param string $merchantAccountId
     */
    public function setMerchantAccountId($merchantAccountId)
    {
        $this->merchantAccountId = $merchantAccountId;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param string $privateKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * @return bool
     */
    public function isVaultModeActive()
    {
        return $this->vault;
    }

    /**
     * @return void
     */
    public function enableVaultMode()
    {
        $this->vault = true;
    }

    /**
     * @return void
     */
    public function disableVaultMode()
    {
        $this->vault = false;
    }

    /**
     * @param bool $vault
     */
    public function setVaultMode($vault)
    {
        $this->vault = $vault;
    }

    /**
     * @@return bool
     */
    public function getVaultMode()
    {
        return $this->vault;
    }

    /**
     * @return array
     */
    public function getPaymentMethodSettings()
    {
        return $this->paymentMethodSettings;
    }

    /**
     * @param array $paymentMethodSettings
     */
    public function setPaymentMethodSettings(array $paymentMethodSettings)
    {
        $this->paymentMethodSettings = $paymentMethodSettings;
    }

    /**
     * @return ParameterBag
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    BraintreeConfig::LABELS_KEY => $this->getLabels(),
                    BraintreeConfig::SHORT_LABELS_KEY => $this->getShortLabels(),
                    BraintreeConfig::ENVIRONMENT_KEY => $this->getEnvironment(),
                    BraintreeConfig::MERCHANT_ACCOUNT_ID_KEY => $this->getMerchantAccountId(),
                    BraintreeConfig::MERCHANT_ID_KEY => $this->getMerchantId(),
                    BraintreeConfig::VAULT_KEY => $this->isVaultModeActive()
                ]
            );
        }

        return $this->settings;
    }
}