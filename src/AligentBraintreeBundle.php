<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle;


use Aligent\BraintreeBundle\DependencyInjection\Compiler\ActionPass;
use Aligent\BraintreeBundle\DependencyInjection\Compiler\PaymentMethodConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AligentBraintreeBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ActionPass())
            ->addCompilerPass(new PaymentMethodConfigurationPass());
    }
}