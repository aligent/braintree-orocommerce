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
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

interface BraintreeViewFactoryInterface
{
    /**
     * @param BraintreeConfigInterface $config
     * @param TokenStorageInterface $tokenStorage
     * @param ChainConfigurationBuilder $configurationBuilder
     * @param DoctrineHelper $doctrineHelper
     * @return PaymentMethodViewInterface
     */
    public function create(BraintreeConfigInterface $config, TokenStorageInterface $tokenStorage, ChainConfigurationBuilder $configurationBuilder, DoctrineHelper $doctrineHelper);
}
