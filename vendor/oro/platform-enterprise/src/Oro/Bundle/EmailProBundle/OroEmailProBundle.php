<?php

namespace Oro\Bundle\EmailProBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\EmailProBundle\DependencyInjection\CompilerPass\SmtpSettingsProviderPass;

class OroEmailProBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'OroEmailBundle';
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SmtpSettingsProviderPass());
    }
}
