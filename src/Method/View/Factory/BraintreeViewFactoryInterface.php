<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 3:34 AM
 */

namespace Aligent\BraintreeBundle\Method\View\Factory;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Aligent\BraintreeBundle\Provider\PaymentMethodSettingsProvider;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

interface BraintreeViewFactoryInterface
{
    /**
     * @param BraintreeConfigInterface $config
     * @param TokenStorageInterface $tokenStorage
     * @param PaymentMethodSettingsProvider $settingsProvider
     * @return PaymentMethodViewInterface
     */
    public function create(BraintreeConfigInterface $config, TokenStorageInterface $tokenStorage, PaymentMethodSettingsProvider $settingsProvider);
}