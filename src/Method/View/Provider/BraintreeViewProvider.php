<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 3:37 AM
 */

namespace Aligent\BraintreeBundle\Method\View\Provider;


use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\ChainConfigurationBuilder;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Aligent\BraintreeBundle\Method\Config\Provider\BraintreeConfigProviderInterface;
use Aligent\BraintreeBundle\Method\View\Factory\BraintreeViewFactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Method\View\AbstractPaymentMethodViewProvider;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BraintreeViewProvider extends AbstractPaymentMethodViewProvider
{
    /** @var BraintreeViewFactoryInterface */
    protected $factory;

    /** @var BraintreeConfigProviderInterface */
    protected $configProvider;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ChainConfigurationBuilder */
    protected $configurationBuilder;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param BraintreeConfigProviderInterface $configProvider
     * @param BraintreeViewFactoryInterface $factory
     * @param TokenStorageInterface $tokenStorage
     * @param ChainConfigurationBuilder $settingsProvider
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        BraintreeConfigProviderInterface $configProvider,
        BraintreeViewFactoryInterface $factory,
        TokenStorageInterface $tokenStorage,
        ChainConfigurationBuilder $settingsProvider,
        DoctrineHelper $doctrineHelper
    ) {
        $this->factory = $factory;
        $this->configProvider = $configProvider;
        $this->tokenStorage = $tokenStorage;
        $this->configurationBuilder = $settingsProvider;
        $this->doctrineHelper = $doctrineHelper;

        parent::__construct();
    }

    /**
     * @return ArrayCollection|PaymentMethodViewInterface[]
     */
    protected function buildViews()
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->addBraintreeView($config);
        }

        return $this->views;
    }

    /**
     * @param BraintreeConfigInterface $config
     */
    protected function addBraintreeView($config)
    {
        $this->addView(
            $config->getPaymentMethodIdentifier(),
            $this->factory->create($config, $this->tokenStorage, $this->configurationBuilder, $this->doctrineHelper)
        );
    }
}