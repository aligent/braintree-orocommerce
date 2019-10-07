<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/2/19
 * Time: 11:17 PM
 */

namespace Aligent\BraintreeBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AligentBraintreeExtension extends Extension
{
    const ALIAS = 'aligent_braintree';

    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('form_types.yml');
        $loader->load('method.yml');
        $loader->load('integration.yml');
        $loader->load('actions.yml');
    }

    public function getAlias()
    {
        return self::ALIAS;
    }
}