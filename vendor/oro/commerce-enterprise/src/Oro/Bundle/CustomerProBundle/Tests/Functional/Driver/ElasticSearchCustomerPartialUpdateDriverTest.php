<?php

namespace Oro\Bundle\CustomerProBundle\Tests\Functional\Driver;

use Oro\Bundle\VisibilityBundle\Tests\Functional\Driver\AbstractCustomerPartialUpdateDriverTest;
use Oro\Bundle\ElasticSearchBundle\Engine\ElasticSearch;

/**
 * @dbIsolationPerTest
 */
class ElasticSearchCustomerPartialUpdateDriverTest extends AbstractCustomerPartialUpdateDriverTest
{
    /**
     * {@inheritdoc}
     */
    protected function isTestSkipped()
    {
        if ($this->getContainer()->getParameter('oro_website_search.engine') !== ElasticSearch::ENGINE_NAME) {
            $this->markTestSkipped('Should be tested only with ElasticSearch search engine');

            return true;
        }

        return false;
    }

    protected function tearDown()
    {
        parent::tearDown();

        $searchIndexer = $this->getContainer()->get('oro_website_search.indexer');
        $searchIndexer->resetIndex();
    }
}
