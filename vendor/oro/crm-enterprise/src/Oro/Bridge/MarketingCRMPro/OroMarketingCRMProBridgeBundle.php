<?php

namespace Oro\Bridge\MarketingCRMPro;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bridge\MarketingCRMPro\DependencyInjection\CompilerPass\CampaignMultiLineDataTransformerPass;

class OroMarketingCRMProBridgeBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CampaignMultiLineDataTransformerPass());
    }
}
