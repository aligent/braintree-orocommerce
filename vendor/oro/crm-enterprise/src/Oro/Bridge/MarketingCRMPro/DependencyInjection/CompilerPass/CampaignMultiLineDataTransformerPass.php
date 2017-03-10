<?php

namespace Oro\Bridge\MarketingCRMPro\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bridge\MarketingCRMPro\Model\Data\Transformer\CampaignMultiLineDataTransformer;
use Symfony\Component\DependencyInjection\Reference;

class CampaignMultiLineDataTransformerPass implements CompilerPassInterface
{
    const TRANSFORMER_ID = 'oro_fusionchart.data_transformer.campaign_multi_line';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $transformer = $container->getDefinition(self::TRANSFORMER_ID);
        $transformer->setClass(CampaignMultiLineDataTransformer::class);

        $transformer->addMethodCall('addFeature', ['campaign']);
        $checkerReference = new Reference('oro_featuretoggle.checker.feature_checker');
        $transformer->addMethodCall('setFeatureChecker', [$checkerReference]);
    }
}
