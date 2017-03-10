<?php

namespace Oro\Bundle\OrganizationConfigBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\OrganizationConfigBundle\DependencyInjection\Compiler\ConfigurationLabelFallbackPass;

class OroOrganizationConfigBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ConfigurationLabelFallbackPass());
    }
}
