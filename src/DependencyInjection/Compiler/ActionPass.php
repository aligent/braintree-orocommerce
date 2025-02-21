<?php
/**
 * Compiler pass to add Action classes to action manager
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\DependencyInjection\Compiler;

use Aligent\BraintreeBundle\Method\Action\Provider\BraintreeActionProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ActionPass implements CompilerPassInterface
{
    const ALIGENT_BRAINTREE_ACTION_TAG = 'braintree.action';

    public function process(ContainerBuilder $container): void
    {
        // always first check if the primary service is defined
        if (!$container->has(BraintreeActionProvider::class)) {
            return;
        }

        $definition = $container->findDefinition(BraintreeActionProvider::class);

        // find all service IDs with the braintree.action tag
        $taggedServices = $container->findTaggedServiceIds(self::ALIGENT_BRAINTREE_ACTION_TAG);

        foreach ($taggedServices as $id => $tags) {
            // a service could have the same tag twice
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addAction',
                    [
                        $attributes["action"],
                        new Reference($id)
                    ]
                );
            }
        }
    }
}
