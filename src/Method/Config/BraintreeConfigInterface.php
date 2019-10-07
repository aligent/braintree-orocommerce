<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 3:28 AM
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

    const PAYPAL_FLOW_VAULT = 'vault';
    const PAYPAL_FLOW_CHECKOUT = 'checkout';
    const PAYPAL_BILLING_PAGE = 'billing';
    const PAYPAL_LOGIN_PAGE = 'login';


    /**
     * @return string
     */
    public function getEnvironment();

    /**
     * @return string
     */
    public function getMerchantId();

    /**
     * @return string
     */
    public function getMerchantAccountId();

    /**
     * @return string
     */
    public function getPublicKey();

    /**
     * @return string
     */
    public function getPrivateKey();

    /**
     * @param $value string
     */
    public function setPublicKey($value);

    /**
     * @param $value string
     */
    public function setPrivateKey($value);

    /**
     * @return bool
     */
    public function isVaultMode();

    /**
     * Are we in sandbox mode
     * @return bool
     */
    public function isSandboxMode();

    /**
     * @return ArrayCollection
     */
    public function getLabels();

    /**
     * @return ArrayCollection
     */
    public function getShortLabels();

    /**
     * @return LocalizedFallbackValue
     */
    public function getLabel();

    /**
     * @return LocalizedFallbackValue
     */
    public function getShortLabel();

    /**
     * @param string $value
     * @return $this
     */
    public function setLabel($value);

    /**
     * @param string $value
     * @return $this
     */
    public function setShortLabel($value);

    /**
     * @return array
     */
    public function getPaymentMethodSettings();

    /**
     * @param array $settings
     */
    public function setPaymentMethodSettings(array $settings);
}