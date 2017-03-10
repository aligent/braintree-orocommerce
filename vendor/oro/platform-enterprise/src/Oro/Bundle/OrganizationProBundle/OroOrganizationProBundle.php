<?php

namespace Oro\Bundle\OrganizationProBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\OrganizationProBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class OroOrganizationProBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }
}
