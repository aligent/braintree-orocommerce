<?php

namespace Oro\Bundle\MultiWebsiteBundle;

use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\Compiler\WebsiteMatcherPass;
use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\OroMultiWebsiteExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroMultiWebsiteBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new WebsiteMatcherPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroMultiWebsiteExtension();
        }

        return $this->extension;
    }
}
