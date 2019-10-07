<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/2/19
 * Time: 11:16 PM
 */

namespace Aligent\BraintreeBundle;


use Aligent\BraintreeBundle\DependencyInjection\Compiler\ActionPass;
use Aligent\BraintreeBundle\DependencyInjection\Compiler\PaymentMethodSettingsPass;
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
            ->addCompilerPass(new PaymentMethodSettingsPass());
    }
}