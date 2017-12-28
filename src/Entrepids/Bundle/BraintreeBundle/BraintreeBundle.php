<?php
namespace Entrepids\Bundle\BraintreeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Entrepids\Bundle\BraintreeBundle\DependencyInjection\BraintreeExtension;

// ORO REVIEW:
// It is a good practice to use company name as a prefix for a bundle name
class BraintreeBundle extends Bundle
{

    /**
     *
     * @ERROR!!!
     *
     */
    public function getContainerExtension()
    {
        if (! $this->extension) {
            $this->extension = new BraintreeExtension();
        }
        
        return $this->extension;
    }
}
