<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\DependencyInjection\Compiler;

use Oro\Bundle\ElasticSearchBundle\DependencyInjection\Compiler\ElasticSearchProviderPass;

class WebsiteElasticSearchEngineConfigPass extends ElasticSearchProviderPass
{
    /**
     * @return string
     */
    public static function getEngineIndexNameKey()
    {
        return 'website_search_engine_index_name';
    }

    /**
     * @return string
     */
    public static function getEngineParametersKey()
    {
        return 'oro_website_search.engine_parameters';
    }
}
