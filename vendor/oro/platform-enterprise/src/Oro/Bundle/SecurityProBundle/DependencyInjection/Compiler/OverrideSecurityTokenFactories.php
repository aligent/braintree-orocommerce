<?php

namespace Oro\Bundle\SecurityProBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideSecurityTokenFactories implements CompilerPassInterface
{
    /**
     * @var array
     */
    // @codingStandardsIgnoreStart
    private static $proTokenFactories = [
        'oro_sso.token.factory.oauth' => 'Oro\Bundle\SecurityProBundle\Tokens\ProOAuthTokenFactory',
        'oro_security.token.factory.organization_rememberme' => 'Oro\Bundle\SecurityProBundle\Tokens\ProOrganizationRememberMeTokenFactory',
        'oro_security.token.factory.username_password_organization'  => 'Oro\Bundle\SecurityProBundle\Tokens\ProUsernamePasswordOrganizationTokenFactory',
        'oro_user.token.factory.wsse' => 'Oro\Bundle\SecurityProBundle\Tokens\ProWsseTokenFactory'
    ];
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach (self::$proTokenFactories as $tokenFactoryServiceId => $proTokenFactoryClass) {
            if ($container->hasDefinition($tokenFactoryServiceId)) {
                $definition = $container->getDefinition($tokenFactoryServiceId);
                $definition->setClass($proTokenFactoryClass);
            }
        }
    }
}
