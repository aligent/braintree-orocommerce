<?php

namespace Oro\Bundle\MultiWebsiteBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WebsiteMatcherPass implements CompilerPassInterface
{
    const REGISTRY_SERVICE = 'oro_multiwebsite.matcher.website_matcher_registry';
    const TAG = 'oro_website_matcher';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_SERVICE)) {
            return;
        }
        
        $matchers = [];
        foreach ($container->findTaggedServiceIds(self::TAG) as $id => $tag) {
            $alias = empty($tag[0]['alias']) ? $id : $tag[0]['alias'];
            $priority = isset($tag[0]['priority']) ? $tag[0]['priority'] : 0;

            $matchers[] = ['id' => $id, 'alias' => $alias, 'priority' => $priority];
        }

        if ($matchers) {
            $registryDefinition = $container->getDefinition(self::REGISTRY_SERVICE);
            foreach ($matchers as $matcherData) {
                $matcher = $container->getDefinition($matcherData['id']);
                $matcher->addMethodCall('setPriority', [$matcherData['priority']]);

                $registryDefinition->addMethodCall(
                    'addMatcher',
                    [
                        $matcherData['alias'],
                        new Reference($matcherData['id'])
                    ]
                );
            }
        }
    }
}
