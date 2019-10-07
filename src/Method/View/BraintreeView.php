<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 3:35 AM
 */

namespace Aligent\BraintreeBundle\Method\View;


use Aligent\BraintreeBundle\Braintree\Gateway;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Aligent\BraintreeBundle\Provider\PaymentMethodSettingsProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\VarDumper\VarDumper;

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
     * @var PaymentMethodSettingsProvider
     */
    protected $settingsProvider;

    /**
     * BraintreeView constructor.
     * @param BraintreeConfigInterface $config
     * @param TokenStorageInterface $tokenStorage
     * @param PaymentMethodSettingsProvider $settingsProvider
     */
    public function __construct(
        BraintreeConfigInterface $config,
        TokenStorageInterface $tokenStorage,
        PaymentMethodSettingsProvider $settingsProvider
    )
    {
        $this->config = $config;
        $this->tokenStorage = $tokenStorage;
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * @param PaymentContextInterface $context
     * @return array
     */
    public function getOptions(PaymentContextInterface $context)
    {
        return [
            'authToken' => $this->getAuthToken(),
            'paymentMethodSettings' => $this->getPaymentMethodSettings($context)
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
     * Create an authentication token for the logged in user
     * @return string
     */
    protected function getAuthToken()
    {
        $gateway = Gateway::getInstance($this->config);

        $token = $this->tokenStorage->getToken();

        if ($token && $this->config->isVaultMode()) {
            $user = $token->getUser();

            if ($user instanceof CustomerUser) {
                return $gateway->getAuthToken(
                    [
                        'customerId' => $user->getId()
                    ]
                );
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
        $settings = [];
        $paymentMethodSettings = $this->config->getPaymentMethodSettings();

        foreach ($paymentMethodSettings as $paymentMethod => $paymentMethodsettings) {
            if ($paymentMethodsettings['enabled']) {
                unset($paymentMethodsettings['enabled']);
                $settings[$paymentMethod] = $this->settingsProvider->build(
                    $paymentMethod,
                    $context,
                    $paymentMethodsettings
                );
            }
        }

        return $settings;
    }
}