<?php

namespace Oro\Bundle\EmailProBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\EmailProBundle\Provider\SmtpSettingsProvider;

class SmtpSettingsProviderPass implements CompilerPassInterface
{
    const PROVIDER_ID = 'oro_email.provider.smtp_settings';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $provider = $container->getDefinition(self::PROVIDER_ID);
        $provider->setClass(SmtpSettingsProvider::class);
        $provider->addMethodCall('setOrgScopeManager', [new Reference('oro_organization_config.scope.organization')]);
    }
}
