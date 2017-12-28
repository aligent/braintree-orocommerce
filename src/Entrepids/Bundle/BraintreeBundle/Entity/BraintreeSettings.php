<?php
namespace Entrepids\Bundle\BraintreeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @ORM\Entity(repositoryClass="Entrepids\Bundle\BraintreeBundle\Entity\Repository\BraintreeSettingsRepository")
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class BraintreeSettings extends Transport
{

    const BRAINTREE_LABEL_KEY = 'braintree_label';

    const BRAINTREE_SHORT_LABEL_KEY = 'braintree_short_label';

    const BRAINTREE_PRO_ALLOWED_CC_TYPES_KEY = 'braintree_allowed_cc_types';

    const BRAINTREE_ENVIRONMENT_TYPES = 'braintree_environment_types';

    const BRAINTREE_SANDBOX_MERCH_ID = 'braintree_sandbox_merch_id';

    const BRAINTREE_SANDBOX_ACCOUNT_ID = 'braintree_sandbox_merch_account_id';

    const BRAINTREE_SANDBOX_PUBLIC_KEY = 'braintree_sandbox_merch_public_key';

    const BRAINTREE_SANDBOX_PRIVATE_KEY = 'braintree_sandbox_merch_private_key';

    const BRAINTREE_CREDIT_CARD_SAFE_FOR_LATER = "braintree_safe_for_later";

    const BRAINTREE_CAPTURE_PAYMENT_ACTION = 'braintree_capture_payment_action';

    const ZERO_AMOUNT_AUTHORIZATION_KEY = 'zero_amount_authorization';

    const AUTHORIZATION_FOR_REQUIRED_AMOUNT_KEY = 'authorization_for_required_amount';

    /**
     *
     * @var ParameterBag
     */
    protected $settings;

    /**
     *
     * @var Collection|LocalizedFallbackValue[] @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     *      )
     *      @ORM\JoinTable(
     *      name="entrepids_braintree_lbl",
     *      joinColumns={
     *      @ORM\JoinColumn(name="transport_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *      @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     *      )
     */
    protected $braintreeLabel;

    /**
     *
     * @var Collection|LocalizedFallbackValue[] @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     *      )
     *      @ORM\JoinTable(
     *      name="entrepids_braintree_sh_lbl",
     *      joinColumns={
     *      @ORM\JoinColumn(name="transport_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *      @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     *      )
     */
    protected $braintreeShortLabel;

    /**
     *
     * @var array @ORM\Column(name="braintree_allowed_card_types", type="array", length=255, nullable=false)
     *
     */
    protected $allowedCreditCardTypes = [];

    /**
     *
     * @var string @ORM\Column(name="braintree_payment_action", type="string", length=255, nullable=false)
     */
    protected $braintreePaymentAction;

    /**
     *
     * @var string @ORM\Column(name="braintree_environment_type", type="string", length=255, nullable=false)
     */
    protected $braintreeEnvironmentType;

    /**
     *
     * @var boolean @ORM\Column(name="braintree_zero_amount", type="boolean", options={"default"=false})
     */
    protected $zeroAmountAuthorization = false;

    /**
     *
     * @var boolean @ORM\Column(name="braintree_auth_for_req_amount", type="boolean", options={"default"=false})
     */
    protected $authorizationForRequiredAmount = false;

    /**
     *
     * @var string @ORM\Column(name="braintree_merch_id", type="string", length=255, nullable=false)
     */
    protected $braintreeMerchId;

    /**
     *
     * @var string @ORM\Column(name="braintree_merch_account_id", type="string", length=255, nullable=false)
     */
    protected $braintreeMerchAccountId;

    /**
     *
     * @var string @ORM\Column(name="braintree_merch_public_key", type="string", length=255, nullable=false)
     */
    protected $braintreeMerchPublicKey;

    /**
     *
     * @var string @ORM\Column(name="braintree_merch_private_key", type="string", length=255, nullable=false)
     */
    protected $braintreeMerchPrivateKey;

    /**
     *
     * @var boolean @ORM\Column(name="braintree_safe_for_later", type="boolean", options={"default"=false})
     */
    protected $saveForLater = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->braintreeLabel = new ArrayCollection();
        $this->braintreeShortLabel = new ArrayCollection();
    }

    /**
     *
     * @return ParameterBag
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag([
                self::BRAINTREE_LABEL_KEY => $this->getBraintreeLabel(),
                self::BRAINTREE_SHORT_LABEL_KEY => $this->getBraintreeShortLabel(),
                self::BRAINTREE_PRO_ALLOWED_CC_TYPES_KEY => $this->getAllowedCreditCardTypes(),
                self::BRAINTREE_ENVIRONMENT_TYPES => $this->getBraintreeEnvironmentType(),
                self::BRAINTREE_SANDBOX_MERCH_ID => $this->getBraintreeMerchId(),
                self::BRAINTREE_SANDBOX_ACCOUNT_ID => $this->getBraintreeMerchAccountId(),
                self::BRAINTREE_SANDBOX_PUBLIC_KEY => $this->getBraintreeMerchPublicKey(),
                self::BRAINTREE_SANDBOX_PRIVATE_KEY => $this->getBraintreeMerchPrivateKey(),
                self::BRAINTREE_CREDIT_CARD_SAFE_FOR_LATER => $this->getSaveForLater(),
                self::BRAINTREE_CAPTURE_PAYMENT_ACTION => $this->getBraintreePaymentAction(),
                self::ZERO_AMOUNT_AUTHORIZATION_KEY => $this->getZeroAmountAuthorization(),
                self::AUTHORIZATION_FOR_REQUIRED_AMOUNT_KEY => $this->getAuthorizationForRequiredAmount()
            ]);
        }
        
        return $this->settings;
    }

    /**
     * Add braintreeLabel
     *
     * @param LocalizedFallbackValue $braintreeLabel
     *
     * @return BraintreeSettings
     */
    public function addBraintreeLabel(LocalizedFallbackValue $braintreeLabel)
    {
        if (! $this->braintreeLabel->contains($braintreeLabel)) {
            $this->braintreeLabel->add($braintreeLabel);
        }
        
        return $this;
    }

    /**
     * Remove braintreeLabel
     *
     * @param LocalizedFallbackValue $braintreeLabel
     *
     * @return BraintreeSettings
     */
    public function removeBraintreeLabel(LocalizedFallbackValue $braintreeLabel)
    {
        if ($this->braintreeLabel->contains($braintreeLabel)) {
            $this->braintreeLabel->removeElement($braintreeLabel);
        }
        
        return $this;
    }

    /**
     * Get braintreeLabel
     *
     * @return Collection
     */
    public function getBraintreeLabel()
    {
        return $this->braintreeLabel;
    }

    /**
     * Add braintreeLabel
     *
     * @param LocalizedFallbackValue $braintreeShortLabel
     *
     * @return BraintreeSettings
     */
    public function addBraintreeShortLabel(LocalizedFallbackValue $braintreeShortLabel)
    {
        if (! $this->braintreeShortLabel->contains($braintreeShortLabel)) {
            $this->braintreeShortLabel->add($braintreeShortLabel);
        }
        
        return $this;
    }

    /**
     * Remove braintreeLabel
     *
     * @param LocalizedFallbackValue $braintreeShortLabel
     *
     * @return BraintreeSettings
     */
    public function removeBraintreeShortLabel(LocalizedFallbackValue $braintreeShortLabel)
    {
        if ($this->braintreeShortLabel->contains($braintreeShortLabel)) {
            $this->braintreeShortLabel->removeElement($braintreeShortLabel);
        }
        
        return $this;
    }

    /**
     * Get braintreeLabel
     *
     * @return Collection
     */
    public function getBraintreeShortLabel()
    {
        return $this->braintreeShortLabel;
    }

    /**
     * Set allowedCreditCardTypes
     *
     * @param array $allowedCreditCardTypes
     *
     * @return BraintreeSettings
     */
    public function setAllowedCreditCardTypes(array $allowedCreditCardTypes)
    {
        $this->allowedCreditCardTypes = $allowedCreditCardTypes;
        
        return $this;
    }

    /**
     * Get allowedCreditCardTypes
     *
     * @return Collection
     */
    public function getAllowedCreditCardTypes()
    {
        return $this->allowedCreditCardTypes;
    }

    /**
     * Set braintreeEnvironmentType
     *
     * @param string $braintreeEnvironmentType
     *
     * @return BraintreeSettings
     */
    public function setBraintreeEnvironmentType($braintreeEnvironmentType)
    {
        $this->braintreeEnvironmentType = $braintreeEnvironmentType;
        
        return $this;
    }

    /**
     * Get braintreeEnvironmentType
     *
     * @return string
     */
    public function getBraintreeEnvironmentType()
    {
        return $this->braintreeEnvironmentType;
    }

    /**
     * Set braintreeMerchId
     *
     * @param string $braintreeMerchId
     *
     * @return BraintreeSettings
     */
    public function setBraintreeMerchId($braintreeMerchId)
    {
        $this->braintreeMerchId = $braintreeMerchId;
        
        return $this;
    }

    /**
     * Get braintreeMerchId
     *
     * @return string
     */
    public function getBraintreeMerchId()
    {
        return $this->braintreeMerchId;
    }

    /**
     * Set braintreeMerchAccountId
     *
     * @param string $braintreeMerchAccountId
     *
     * @return BraintreeSettings
     */
    public function setBraintreeMerchAccountId($braintreeMerchAccountId)
    {
        $this->braintreeMerchAccountId = $braintreeMerchAccountId;
        
        return $this;
    }

    /**
     * Get braintreeMerchAccountId
     *
     * @return string
     */
    public function getBraintreeMerchAccountId()
    {
        return $this->braintreeMerchAccountId;
    }

    /**
     * Set braintreeMerchPublicKey
     *
     * @param string $braintreeMerchPublicKey
     *
     * @return BraintreeSettings
     */
    public function setBraintreeMerchPublicKey($braintreeMerchPublicKey)
    {
        $this->braintreeMerchPublicKey = $braintreeMerchPublicKey;
        
        return $this;
    }

    /**
     * Get braintreeMerchPublicKey
     *
     * @return string
     */
    public function getBraintreeMerchPublicKey()
    {
        return $this->braintreeMerchPublicKey;
    }

    /**
     * Set braintreeMerchPrivateKey
     *
     * @param string $braintreeMerchPrivateKey
     *
     * @return BraintreeSettings
     */
    public function setBraintreeMerchPrivateKey($braintreeMerchPrivateKey)
    {
        $this->braintreeMerchPrivateKey = $braintreeMerchPrivateKey;
        
        return $this;
    }

    /**
     * Get braintreeMerchPrivateKey
     *
     * @return string
     */
    public function getBraintreeMerchPrivateKey()
    {
        return $this->braintreeMerchPrivateKey;
    }

    /**
     * Set saveForLater
     *
     * @param boolean $saveForLater
     *
     * @return BraintreeSettings
     */
    public function setSaveForLater($saveForLater)
    {
        $this->saveForLater = $saveForLater;
        
        return $this;
    }

    /**
     * Get saveForLater
     *
     * @return boolean
     */
    public function getSaveForLater()
    {
        return $this->saveForLater;
    }

    /**
     * Set braintreePaymentAction
     *
     * @param string $braintreePaymentAction
     *
     * @return BraintreeSettings
     */
    public function setBraintreePaymentAction($braintreePaymentAction)
    {
        $this->braintreePaymentAction = $braintreePaymentAction;
        
        return $this;
    }

    /**
     * Get braintreePaymentAction
     *
     * @return string
     */
    public function getBraintreePaymentAction()
    {
        return $this->braintreePaymentAction;
    }

    /**
     * Get zeroAmountAuthorization
     *
     * @return boolean
     */
    public function getZeroAmountAuthorization()
    {
        return $this->zeroAmountAuthorization;
    }

    /**
     * Get authorizationForRequiredAmount
     *
     * @return boolean
     */
    public function getAuthorizationForRequiredAmount()
    {
        return $this->authorizationForRequiredAmount;
    }
}
