<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\View;

use Aligent\BraintreeBundle\Braintree\Gateway;
use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\ConfigurationBuilderInterface;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BraintreeView implements PaymentMethodViewInterface
{
    /**
     * @var BraintreeConfigInterface
     */
    protected $config;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var ConfigurationBuilderInterface
     */
    protected $configurationBuilder;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * BraintreeView constructor.
     * @param BraintreeConfigInterface $config
     * @param TokenStorageInterface $tokenStorage
     * @param ConfigurationBuilderInterface $configurationBuilder
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        BraintreeConfigInterface $config,
        TokenStorageInterface $tokenStorage,
        ConfigurationBuilderInterface $configurationBuilder,
        DoctrineHelper $doctrineHelper
    ) {
        $this->config = $config;
        $this->tokenStorage = $tokenStorage;
        $this->configurationBuilder = $configurationBuilder;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * These keys are used in the javascript file 'braintree-method-component.js to retrieve data from backend
     * @param PaymentContextInterface $context
     * @return array
     */
    public function getOptions(PaymentContextInterface $context)
    {
        return [
            'authToken' => $this->getAuthToken(),
            'paymentMethodSettings' => $this->getPaymentMethodSettings($context),
            'vaultMode' => $this->config->isVaultMode(),
            'fraudProtectionAdvanced' => $this->config->isFraudProtectionAdvancedEnabled(),
        ];
    }

    /**
     * @return string
     */
    public function getBlock()
    {
        return '_payment_methods_aligent_braintree_widget';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->config->getLabel();
    }

    /**
     * @return string
     */
    public function getAdminLabel()
    {
        return $this->config->getAdminLabel();
    }

    /**
     * @return string
     */
    public function getShortLabel()
    {
        return $this->config->getShortLabel();
    }

    /**
     * @return string
     */
    public function getPaymentMethodIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    /**
     * Create an authentication token for the logged in user (if vault mode is enabled)
     * or a generic token (if vault mode is disabled)
     * @return string
     */
    protected function getAuthToken()
    {
        $gateway = new Gateway($this->config, $this->doctrineHelper);

        $token = $this->tokenStorage->getToken();

        if ($token && $this->config->isVaultMode()) {
            $user = $token->getUser();

            if ($user instanceof CustomerUser) {
                return $gateway->getCustomerAuthToken($user);
            }
        }

        return $gateway->getAuthToken();
    }

    /**
     * @param PaymentContextInterface $context
     * @return array
     */
    protected function getPaymentMethodSettings(PaymentContextInterface $context)
    {
        $paymentMethodSettings = $this->config->getPaymentMethodSettings();
        return $this->configurationBuilder->build($context, $paymentMethodSettings);
    }
}
