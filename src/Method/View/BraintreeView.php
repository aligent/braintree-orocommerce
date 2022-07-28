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
    protected BraintreeConfigInterface $config;
    protected TokenStorageInterface $tokenStorage;
    protected ConfigurationBuilderInterface $configurationBuilder;
    protected DoctrineHelper $doctrineHelper;

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
     * @return array<string,mixed>
     */
    public function getOptions(PaymentContextInterface $context): array
    {
        return [
            'authToken' => $this->getAuthToken(),
            'paymentMethodSettings' => $this->getPaymentMethodSettings($context),
            'vaultMode' => $this->config->isVaultMode(),
            'fraudProtectionAdvanced' => $this->config->isFraudProtectionAdvancedEnabled(),
        ];
    }

    public function getBlock(): string
    {
        return '_payment_methods_aligent_braintree_widget';
    }

    public function getLabel(): string
    {
        return $this->config->getLabel();
    }

    /**
     * @return string
     */
    public function getAdminLabel(): string
    {
        return $this->config->getAdminLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getShortLabel();
    }

    public function getPaymentMethodIdentifier(): string
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    /**
     * Create an authentication token for the logged-in user (if vault mode is enabled)
     * or a generic token (if vault mode is disabled)
     */
    protected function getAuthToken(): string
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
     * @return array<string,mixed>
     */
    protected function getPaymentMethodSettings(PaymentContextInterface $context): array
    {
        $paymentMethodSettings = $this->config->getPaymentMethodSettings();
        return $this->configurationBuilder->build($context, $paymentMethodSettings);
    }
}
