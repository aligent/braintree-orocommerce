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
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\PaymentBundle\Method\Config\ParameterBag\AbstractParameterBagPaymentConfig;

class BraintreeConfig extends AbstractParameterBagPaymentConfig implements BraintreeConfigInterface
{
    /**
     * @return string
     */
    public function getEnvironment()
    {
        return (string) $this->get(self::ENVIRONMENT_KEY);
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return (string) $this->get(self::MERCHANT_ID_KEY);
    }

    /**
     * @return string
     */
    public function getMerchantAccountId()
    {
        return (string) $this->get(self::MERCHANT_ACCOUNT_ID_KEY);
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return (string) $this->get(self::PUBLIC_KEY_KEY);
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return (string) $this->get(self::PRIVATE_KEY_KEY);
    }

    /**
     * @param $value string
     */
    public function setPublicKey($value)
    {
        $this->set(self::PUBLIC_KEY_KEY, $value);
    }

    /**
     * @param $value string
     */
    public function setPrivateKey($value)
    {
        $this->set(self::PRIVATE_KEY_KEY, $value);
    }

    /**
     * @return bool
     */
    public function isVaultMode()
    {
        return (boolean) $this->get(self::VAULT_KEY);
    }

    /**
     * Are we in sandbox mode
     * @return bool
     */
    public function isSandboxMode()
    {
        return $this->getEnvironment() === Gateway::SANDBOX;
    }

    /**
     * @return ArrayCollection
     */
    public function getLabels()
    {
        return $this->get(self::LABELS_KEY);
    }

    /**
     * @return ArrayCollection
     */
    public function getShortLabels()
    {
        return $this->get(self::SHORT_LABELS_KEY);
    }

    /**
     * @return LocalizedFallbackValue
     */
    public function getLabel()
    {
        return $this->get(self::LABEL_KEY);
    }

    /**
     * @return LocalizedFallbackValue
     */
    public function getShortLabel()
    {
        return $this->get(self::SHORT_LABEL_KEY);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setLabel($value)
    {
        $this->set(self::LABEL_KEY, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setShortLabel($value)
    {
        $this->set(self::LABEL_KEY, $value);
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentMethodSettings()
    {
        return $this->get(self::PAYMENT_METHODS_CONFIG_KEY);
    }

    /**
     * @param array $settings
     * @return BraintreeConfig
     */
    public function setPaymentMethodSettings(array $settings)
    {
        $this->set(self::PAYMENT_METHODS_CONFIG_KEY, $settings);
        return $this;
    }

    /**
     * @return bool
     */
    public function isFraudProtectionAdvancedEnabled()
    {
        return (boolean) $this->get(self::FRAUD_PROTECTION_ADVANCED_KEY);
    }
}
