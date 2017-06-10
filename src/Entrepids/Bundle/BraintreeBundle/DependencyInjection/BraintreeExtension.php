<?php

namespace Entrepids\Bundle\BraintreeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class BraintreeExtension extends Extension
{
	
	const ALIAS = 'braintree';
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('braintree.yml');
        $loader->load('services.yml');
        $loader->load('form_types.yml');
        $loader->load('block_types.yml');
        $loader->load('method.yml');
        $loader->load('factory.yml');
        $loader->load('integration.yml');
       // $loader->load('listeners.yml');        
        
    }
    
    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
    	return self::ALIAS;
    }
}
