<?php
/**
 * Compiler pass to manager operation classes
 *
 * @category  Braintree
 * @package
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2018 Aligent Consulting
 * @license   Proprietary
 * @link      http://www.aligent.com.au/
 **/

namespace Entrepids\Bundle\BraintreeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OperationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('entrepids_braintree.method.operation.factory')) {
            return;
        }

        $definition = $container->findDefinition('entrepids_braintree.method.operation.factory');

        // find all service IDs with the braintree.operation tag
        $taggedServices = $container->findTaggedServiceIds('braintree.operation');

        foreach ($taggedServices as $id => $tags) {
            // a service could have the same tag twice
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addOperation', array(
                    new Reference($id),
                    $attributes["operation"]
                ));
            }
        }
    }
}
