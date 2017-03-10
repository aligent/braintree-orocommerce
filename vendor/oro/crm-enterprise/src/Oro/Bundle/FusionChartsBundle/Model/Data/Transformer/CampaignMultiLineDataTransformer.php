<?php

namespace Oro\Bundle\FusionChartsBundle\Model\Data\Transformer;

use Oro\Bundle\ChartBundle\Model\Data\ArrayData;
use Oro\Bundle\ChartBundle\Model\Data\DataInterface;
use Oro\Bundle\ChartBundle\Model\Data\Transformer\TransformerInterface;

/**
 * The real implementation of this class is at
 * \Oro\Bridge\MarketingCRMPro\Model\Data\Transformer\CampaignMultiLineDataTransformer
 */
class CampaignMultiLineDataTransformer implements TransformerInterface
{
    /**
     * @param DataInterface $data
     * @param array         $chartOptions
     *
     * @return DataInterface
     */
    public function transform(DataInterface $data, array $chartOptions)
    {
        return new ArrayData([]);
    }
}
