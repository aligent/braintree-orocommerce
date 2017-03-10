<?php

namespace Oro\Bundle\ElasticSearchBundle;

use Oro\Bundle\ElasticSearchBundle\DependencyInjection\Compiler\ElasticSearchProviderPass;
use Oro\Bundle\ElasticSearchBundle\DependencyInjection\Compiler\ElasticSearchRequestBuilderPass;
use Oro\Bundle\ElasticSearchBundle\DependencyInjection\Compiler\ElasticSearchRequestBuilderWherePass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroElasticSearchBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ElasticSearchProviderPass());
        $container->addCompilerPass(new ElasticSearchRequestBuilderPass());
        $container->addCompilerPass(new ElasticSearchRequestBuilderWherePass());
    }
}
