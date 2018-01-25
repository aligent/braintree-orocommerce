<?php
namespace Entrepids\Bundle\BraintreeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Entrepids\Bundle\BraintreeBundle\DependencyInjection\EntrepidsBraintreeExtension;

class EntrepidsBraintreeBundle extends Bundle
{

    /**
     *
     * {@inheritDoc}
     *
     */
    public function getContainerExtension()
    {
        if (! $this->extension) {
            $this->extension = new EntrepidsBraintreeExtension();
        }
        
        return $this->extension;
    }
}
