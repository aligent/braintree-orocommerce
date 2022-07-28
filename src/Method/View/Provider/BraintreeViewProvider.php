<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
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
    protected BraintreeViewFactoryInterface $factory;
    protected BraintreeConfigProviderInterface $configProvider;
    protected TokenStorageInterface $tokenStorage;
    protected ChainConfigurationBuilder $configurationBuilder;
    protected DoctrineHelper $doctrineHelper;

    public function __construct(
        BraintreeConfigProviderInterface $configProvider,
        BraintreeViewFactoryInterface $factory,
        TokenStorageInterface $tokenStorage,
        ChainConfigurationBuilder $settingsProvider,
        DoctrineHelper $doctrineHelper,
    ) {
        $this->factory = $factory;
        $this->configProvider = $configProvider;
        $this->tokenStorage = $tokenStorage;
        $this->configurationBuilder = $settingsProvider;
        $this->doctrineHelper = $doctrineHelper;

        parent::__construct();
    }

    /**
     * @return ArrayCollection<int,PaymentMethodViewInterface>
     */
    protected function buildViews(): ArrayCollection
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->addBraintreeView($config);
        }

        return $this->views;
    }

    protected function addBraintreeView(BraintreeConfigInterface $config): void
    {
        $this->addView(
            $config->getPaymentMethodIdentifier(),
            $this->factory->create($config, $this->tokenStorage, $this->configurationBuilder, $this->doctrineHelper)
        );
    }
}
