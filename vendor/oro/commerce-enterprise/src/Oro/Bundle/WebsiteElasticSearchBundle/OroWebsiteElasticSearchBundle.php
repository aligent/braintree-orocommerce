<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\WebsiteElasticSearchBundle\DependencyInjection\Compiler\WebsiteElasticSearchEngineConfigPass;
use Oro\Bundle\WebsiteElasticSearchBundle\DependencyInjection\Compiler\WebsiteElasticSearchPlaceholderCompilerPass;

class OroWebsiteElasticSearchBundle extends Bundle
{
    /** {@inheritdoc} */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new WebsiteElasticSearchEngineConfigPass());
        $container->addCompilerPass(new WebsiteElasticSearchPlaceholderCompilerPass());
    }
}
