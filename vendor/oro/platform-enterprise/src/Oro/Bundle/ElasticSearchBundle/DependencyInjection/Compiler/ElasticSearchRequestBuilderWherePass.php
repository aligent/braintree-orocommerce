<?php

namespace Oro\Bundle\ElasticSearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ElasticSearchRequestBuilderWherePass implements CompilerPassInterface
{
    const REGISTRY_SERVICE = 'oro_elasticsearch.request_builder.where.part_builder_registry';
    const TAG = 'oro_elasticsearch.request_builder.where';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        if (empty($taggedServices)) {
            return;
        }

        $registryDefinition = $container->getDefinition(self::REGISTRY_SERVICE);

        foreach (array_keys($taggedServices) as $method) {
            $registryDefinition->addMethodCall('addWherePartBuilder', [new Reference($method)]);
        }
    }
}
