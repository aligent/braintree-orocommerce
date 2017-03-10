<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OroWebsiteElasticSearchExtension extends Extension
{
    const ALIAS = 'oro_website_elastic_search';

    /** {@inheritdoc} */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /** {@inheritdoc} */
    public function getAlias()
    {
        return self::ALIAS;
    }
}
