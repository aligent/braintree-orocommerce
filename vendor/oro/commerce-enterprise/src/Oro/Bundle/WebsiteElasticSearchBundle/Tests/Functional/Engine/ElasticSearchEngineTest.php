<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Tests\Functional\Engine;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\ElasticSearchBundle\Engine\ElasticSearch;
use Oro\Bundle\SearchBundle\Tests\Functional\Controller\DataFixtures\LoadSearchItemData;
use Oro\Bundle\SearchBundle\Tests\Functional\SearchExtensionTrait;
use Oro\Bundle\WebsiteSearchBundle\Engine\AbstractEngine;
use Oro\Bundle\WebsiteSearchBundle\Tests\Functional\Engine\AbstractEngineTest;
use Oro\Bundle\WebsiteSearchBundle\Tests\Functional\Traits\DefaultWebsiteIdTestTrait;
use Oro\Bundle\TestFrameworkBundle\Entity\Item as TestEntity;

/**
 * @dbIsolationPerTest
 */
class ElasticSearchEngineTest extends AbstractEngineTest
{
    use SearchExtensionTrait;
    use DefaultWebsiteIdTestTrait;

    protected function setUp()
    {
        $this->initClient();

        $this->getContainer()->get('request_stack')->push(Request::create(''));

        if ($this->getContainer()->getParameter('oro_website_search.engine') !== ElasticSearch::ENGINE_NAME) {
            $this->markTestSkipped(
                sprintf('Should be tested only with "%s" search engine', ElasticSearch::ENGINE_NAME)
            );
        }

        $indexer = $this->getContainer()->get('oro_website_search.indexer');
        $indexer->resetIndex();
        $alias = 'oro_test_item_' . $this->getDefaultWebsiteId();
        $this->ensureItemsLoaded($alias, 0, 'oro_website_search.engine');

        parent::setUp();

        $indexer->reindex(TestEntity::class);
        $this->ensureItemsLoaded($alias, LoadSearchItemData::COUNT, 'oro_website_search.engine');
    }

    public function testRecordUrlForSearchAll()
    {
        $this->markTestSkipped('BB-5220 ES does not have recordTitle in search Item.');
    }

    /**
     * @return AbstractEngine
     */
    protected function getSearchEngine()
    {
        return $this->getContainer()->get('oro_website_search.engine');
    }
}
