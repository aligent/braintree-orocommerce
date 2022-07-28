<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\View\Factory;

use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\ChainConfigurationBuilder;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Aligent\BraintreeBundle\Method\View\BraintreeView;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BraintreeViewFactory implements BraintreeViewFactoryInterface
{
    public function create(
        BraintreeConfigInterface $config,
        TokenStorageInterface $tokenStorage,
        ChainConfigurationBuilder $configurationBuilder,
        DoctrineHelper $doctrineHelper
    ): PaymentMethodViewInterface {
        return new BraintreeView($config, $tokenStorage, $configurationBuilder, $doctrineHelper);
    }
}
