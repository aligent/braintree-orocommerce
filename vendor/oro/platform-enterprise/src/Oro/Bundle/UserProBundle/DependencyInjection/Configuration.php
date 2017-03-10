<?php

namespace Oro\Bundle\UserProBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder  = new TreeBuilder();
        $rootNode = $builder->root('oro_user_pro');

        SettingsBuilder::append(
            $rootNode,
            [
                'failed_login_limit_enabled'          => ['value' => true, 'type' => 'boolean'],
                'failed_login_limit'                  => ['value' => 10, 'type' => 'scalar'],
                'password_special_chars'              => ['value' => false, 'type' => 'boolean'],
                'password_change_period_enabled'      => ['value' => false, 'type' => 'boolean'],
                'password_change_period'              => ['value' => 30, 'type' => 'integer'],
                'password_change_notification_days'   => ['value' => [1, 3, 7], 'type' => 'array'],
                'used_password_check_enabled'         => ['value' => false, 'type' => 'boolean'],
                'used_password_check_number'          => ['value' => 12, 'type' => 'integer'],
                'two_factor_authentication_strength'  => ['value' => 'disabled', 'type' => 'string'],
                'authentication_code_validity_period' => ['value' => '3600', 'type' => 'text'],
                'authentication_code_length'          => ['value' => 6, 'type' => 'integer'],
            ]
        );

        return $builder;
    }
}
