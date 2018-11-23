<?php

namespace Entrepids\Bundle\BraintreeBundle;

use Entrepids\Bundle\BraintreeBundle\DependencyInjection\Compiler\OperationPass;
use Entrepids\Bundle\BraintreeBundle\DependencyInjection\EntrepidsBraintreeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EntrepidsBraintreeBundle extends Bundle
{

    /**
     * @inheritDoc
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new EntrepidsBraintreeExtension();
        }

        return $this->extension;
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OperationPass());
    }
}
