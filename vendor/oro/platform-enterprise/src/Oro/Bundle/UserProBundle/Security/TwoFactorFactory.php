<?php

namespace Oro\Bundle\UserProBundle\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class TwoFactorFactory extends AbstractFactory
{
    /**
     * @var array
     */
    protected $options = [
        'login_path' => 'oropro_user_two_factor_auth',
        'check_path' => 'oropro_user_two_factor_check',
        'auth_parameter' => '_auth_code',
        'require_previous_session' => true,
    ];

    /**
     * {@inheritdoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'security.authentication.provider.two_factor.' . $id;
        $container
            ->setDefinition(
                $providerId,
                new DefinitionDecorator('oro_userpro.security.authentication.two_factor_provider')
            )
            ->replaceArgument(2, new Reference($userProviderId))
            ->replaceArgument(3, $id)
        ;

        return $providerId;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenerId()
    {
        return 'oro_userpro.security.authentication.two_factor_listener';
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'remember_me';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'two-factor';
    }
}
