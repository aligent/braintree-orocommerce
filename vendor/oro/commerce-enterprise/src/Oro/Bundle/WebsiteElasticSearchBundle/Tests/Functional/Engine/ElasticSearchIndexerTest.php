<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Tests\Functional\Engine;

use Oro\Bundle\TestFrameworkBundle\Entity\TestEmployee;
use Oro\Bundle\TestFrameworkBundle\Entity\TestProduct;
use Oro\Bundle\WebsiteElasticSearchBundle\Engine\ElasticSearchIndexer;
use Oro\Bundle\WebsiteElasticSearchBundle\Engine\IndexAgent;
use Oro\Bundle\WebsiteElasticSearchBundle\Provider\WebsiteElasticSearchMappingProvider;
use Oro\Bundle\WebsiteSearchBundle\Engine\AbstractIndexer;
use Oro\Bundle\WebsiteSearchBundle\Entity\Item;
use Oro\Bundle\WebsiteSearchBundle\Event\RestrictIndexEntityEvent;
use Oro\Bundle\WebsiteSearchBundle\Tests\Functional\AbstractSearchWebTestCase;
use Oro\Bundle\WebsiteSearchBundle\Tests\Functional\DataFixtures\LoadEmployeesToIndex;
use Oro\Bundle\WebsiteSearchBundle\Tests\Functional\DataFixtures\LoadOtherWebsite;
use Oro\Bundle\WebsiteSearchBundle\Tests\Functional\DataFixtures\LoadProductsToIndex;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ElasticSearchIndexerTest extends AbstractSearchWebTestCase
{
    /** @var IndexAgent */
    protected $indexAgent;

    /** @var ElasticSearchIndexer */
    protected $indexer;

    /** @var WebsiteElasticSearchMappingProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $mappingProviderMock;

    protected function setUp()
    {
        parent::setUp();

        $this->mappingProviderMock = $this->getMockBuilder(WebsiteElasticSearchMappingProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexer = new ElasticSearchIndexer(
            $this->doctrineHelper,
            $this->mappingProviderMock,
            $this->getContainer()->get('oro_website_search.engine.entity_dependencies_resolver'),
            $this->getContainer()->get('oro_website_search.engine.index_data'),
            $this->getContainer()->get('oro_website_search.placeholder_decorator')
        );

        $this->indexAgent = new IndexAgent(
            $this->getContainer()->get('oro_elasticsearch.client.factory'),
            $this->getContainer()->get('oro_elasticsearch.plugin.verifier'),
            $this->mappingProviderMock,
            $this->getContainer()->getParameter('oro_website_search.engine_parameters'),
            $this->getContainer()->get('oro_website_elastic_search.placeholder_decorator'),
            $this->getContainer()->get('oro_website_search.website_id.placeholder')
        );
        $this->indexAgent->setFieldTypeMapping(
            $this->getContainer()->getParameter('oro_elasticsearch.field_type_mapping')
        );

        $this->indexer->setIndexAgent($this->indexAgent);
        $this->indexer->setPlaceholderHelper(
            $this->getContainer()->get('oro_website_elastic_search.helper.placeholder_helper')
        );

        // Remove index if it exists before test
        $indexName = $this->indexAgent->getIndexName();
        if ($this->indexAgent->isIndexExists($indexName)) {
            $this->indexAgent->getClient()->indices()->delete(['index' => $indexName]);
        }
    }

    /**
     * @param array $options
     * @return Item[]
     */
    protected function getResultItems(array $options)
    {
        if (!empty($options['items_count'])) {
            $this->ensureItemsLoaded($options);
        }

        $result = $this->searchData($options);

        return $this->createItemsByResult($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function preSetUp()
    {
        $this->checkEngine();
    }

    protected function preTearDown()
    {
        $this->checkEngine();
    }

    protected function checkEngine()
    {
        if ($this->getContainer()->getParameter('oro_website_search.engine') !== 'elastic_search') {
            $this->markTestSkipped('Should be tested only with elastic_search engine');
        }
    }

    /**
     * @param array $options
     * @return array
     */
    private function searchData($options)
    {
        $params = [
            'index' => $this->indexAgent->getIndexName(),
            'body' => []
        ];

        if (!empty($options['alias'])) {
            $params['type'] = $options['alias'];
        }

        return $this->indexAgent->getClient()->search($params);
    }

    /**
     * @param array $result
     * @return array
     */
    private function createItemsByResult(array $result)
    {
        $items = [];

        if (!empty($result['hits']['hits'])) {
            foreach ($result['hits']['hits'] as $data) {
                $item = new Item();
                $item
                    ->setAlias($data['_type'])
                    ->setTitle($data['_source']['name_' . $this->getDefaultLocalizationId()]);

                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @param int $expectedCount
     */
    protected function assertItemsCount($expectedCount)
    {
        $this->ensureItemsLoaded(['items_count' => $expectedCount]);

        $result = $this->indexAgent->getClient()->count(['index' => $this->indexAgent->getIndexName()]);
        $count = $result['count'];

        $this->assertSame($expectedCount, $count);
    }

    /**
     * Ensure that items are loaded to search index
     *
     * @param array $options
     * @throws \LogicException
     */
    protected function ensureItemsLoaded($options)
    {
        $requestCounts = 5;

        do {
            $result = $this->searchData($options);
            $actualLoaded = $result['hits']['total'];
            $isLoaded = $actualLoaded === $options['items_count'];
            if (!$isLoaded) {
                $requestCounts--;
                sleep(1);
            }
        } while (!$isLoaded && $requestCounts > 0);

        if (!$isLoaded) {
            throw new \LogicException(
                sprintf(
                    'Incorrect search items in index. Expected: %d, actual: %d',
                    $options['items_count'],
                    $actualLoaded
                )
            );
        }
    }

    public function testResetIndexForAllWebsites()
    {
        $this->prepareDataWithAllWebsitesWithReindex();
        $this->assertItemsCount(4);

        $this->indexer->resetIndex(TestProduct::class, []);
        $this->assertItemsCount(0);
    }

    public function testResetIndexForAllWebsitesAndTwoClasses()
    {
        $this->loadFixtures([LoadOtherWebsite::class, LoadEmployeesToIndex::class, LoadProductsToIndex::class]);

        $this->mappingProviderMock
            ->expects($this->any())
            ->method('isClassSupported')
            ->withConsecutive([TestProduct::class], [TestEmployee::class])
            ->willReturn(true);

        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class, TestEmployee::class]);

        $this->assertItemsCount(8);

        $this->indexer->resetIndex([TestProduct::class, TestEmployee::class], []);
        $this->assertItemsCount(0);
    }

    public function testResetIndexForAllWebsitesAndOneClass()
    {
        $this->loadFixtures([LoadOtherWebsite::class, LoadEmployeesToIndex::class, LoadProductsToIndex::class]);

        $this->mappingProviderMock
            ->expects($this->any())
            ->method('isClassSupported')
            ->withConsecutive([TestProduct::class], [TestEmployee::class])
            ->willReturn(true);

        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class, TestEmployee::class]);

        $this->assertItemsCount(8);

        $this->indexer->resetIndex(TestProduct::class, []);
        $this->assertItemsCount(4);
    }

    public function testResetIndexForOneWebsiteAndOneClass()
    {
        $this->loadFixtures([LoadOtherWebsite::class, LoadEmployeesToIndex::class, LoadProductsToIndex::class]);

        $this->mappingProviderMock
            ->expects($this->any())
            ->method('isClassSupported')
            ->withConsecutive([TestProduct::class], [TestEmployee::class])
            ->willReturn(true);

        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class, TestEmployee::class]);

        $this->assertItemsCount(8);

        $this->indexer->resetIndex(
            TestProduct::class,
            [AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY => $this->getDefaultWebsiteId()]
        );

        $this->assertItemsCount(6);
    }

    public function testResetIndexForOneWebsiteAndTwoClass()
    {
        $this->loadFixtures([LoadOtherWebsite::class, LoadEmployeesToIndex::class, LoadProductsToIndex::class]);

        $this->mappingProviderMock
            ->expects($this->any())
            ->method('isClassSupported')
            ->withConsecutive([TestProduct::class], [TestEmployee::class])
            ->willReturn(true);

        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class, TestEmployee::class]);

        $this->assertItemsCount(8);

        $this->indexer->resetIndex(
            [TestProduct::class, TestEmployee::class],
            [AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY => $this->getDefaultWebsiteId()]
        );

        $this->assertItemsCount(4);
    }

    public function testResetIndexForWebsite()
    {
        $this->loadFixtures([LoadOtherWebsite::class, LoadEmployeesToIndex::class, LoadProductsToIndex::class]);

        $this->mappingProviderMock
            ->expects($this->any())
            ->method('isClassSupported')
            ->withConsecutive([TestProduct::class], [TestEmployee::class])
            ->willReturn(true);

        $this->mappingProviderMock
            ->expects($this->once())
            ->method('getEntityClasses')
            ->willReturn([TestProduct::class, TestEmployee::class]);

        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class, TestEmployee::class]);
        $this->assertItemsCount(8);

        $this->indexer->resetIndex(
            null,
            [AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY => $this->getDefaultWebsiteId()]
        );

        $this->assertItemsCount(4);
    }

    public function testDeleteForAllWebsitesAndTwoClasses()
    {
        $this->loadFixtures([LoadOtherWebsite::class, LoadEmployeesToIndex::class, LoadProductsToIndex::class]);

        $product1 = $this->getReference(LoadProductsToIndex::REFERENCE_PRODUCT1);
        $employee1 = $this->getReference(LoadEmployeesToIndex::REFERENCE_PERSON1);

        $this->mappingProviderMock
            ->expects($this->any())
            ->method('isClassSupported')
            ->withConsecutive([TestProduct::class], [TestEmployee::class])
            ->willReturn(true);

        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class, TestEmployee::class]);

        $this->assertItemsCount(8);

        $this->indexer->delete([$product1, $employee1], []);
        $this->assertItemsCount(4);
    }

    public function testDeleteForAllWebsitesAndOneClass()
    {
        $this->loadFixtures([LoadOtherWebsite::class, LoadEmployeesToIndex::class, LoadProductsToIndex::class]);

        $product1 = $this->getReference(LoadProductsToIndex::REFERENCE_PRODUCT1);

        $this->mappingProviderMock
            ->expects($this->any())
            ->method('isClassSupported')
            ->withConsecutive([TestProduct::class], [TestEmployee::class])
            ->willReturn(true);

        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class, TestEmployee::class]);

        $this->assertItemsCount(8);

        $this->indexer->delete($product1, []);
        $this->assertItemsCount(6);
    }

    public function testDeleteForOneWebsiteAndOneClass()
    {
        $this->loadFixtures([LoadOtherWebsite::class, LoadEmployeesToIndex::class, LoadProductsToIndex::class]);

        $product1 = $this->getReference(LoadProductsToIndex::REFERENCE_PRODUCT1);

        $this->mappingProviderMock
            ->expects($this->any())
            ->method('isClassSupported')
            ->withConsecutive([TestProduct::class], [TestEmployee::class])
            ->willReturn(true);

        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class, TestEmployee::class]);

        $this->assertItemsCount(8);

        $this->indexer->delete(
            $product1,
            [AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY => $this->getDefaultWebsiteId()]
        );

        $this->assertItemsCount(7);
    }

    public function testDeleteForOneWebsiteAndTwoClass()
    {
        $this->loadFixtures([LoadOtherWebsite::class, LoadEmployeesToIndex::class, LoadProductsToIndex::class]);

        $product1 = $this->getReference(LoadProductsToIndex::REFERENCE_PRODUCT1);
        $employee1 = $this->getReference(LoadEmployeesToIndex::REFERENCE_PERSON1);

        $this->mappingProviderMock
            ->expects($this->any())
            ->method('isClassSupported')
            ->withConsecutive([TestProduct::class], [TestEmployee::class])
            ->willReturn(true);

        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class, TestEmployee::class]);

        $this->assertItemsCount(8);

        $this->indexer->delete(
            [$product1, $employee1],
            [AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY => $this->getDefaultWebsiteId()]
        );

        $this->assertItemsCount(6);
    }

    public function testResetIndexForSpecificWebsite()
    {
        $this->prepareDataWithAllWebsitesWithReindex();
        $this->assertItemsCount(4);

        $this->indexer->resetIndex(
            TestProduct::class,
            [AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY => $this->getDefaultWebsiteId()]
        );

        $this->assertItemsCount(2);
    }

    public function testResetIndexForAllClasses()
    {
        $this->prepareDataWithReindex();
        $this->assertItemsCount(2);

        $this->indexer->resetIndex();
        $this->assertItemsCount(0);
    }

    public function testDeleteEntityForAllWebsites()
    {
        $this->prepareDataWithAllWebsitesWithReindex();
        $this->assertItemsCount(4);

        $product1 = $this->getReference(LoadProductsToIndex::REFERENCE_PRODUCT1);

        $this->indexer->delete($product1);
        $this->assertItemsCount(2);
    }

    public function testDeleteEntityForWebsite()
    {
        $this->prepareDataWithAllWebsitesWithReindex();
        $this->assertItemsCount(4);

        $product1 = $this->getReference(LoadProductsToIndex::REFERENCE_PRODUCT1);

        $this->indexer->delete(
            $product1,
            [AbstractIndexer::CONTEXT_CURRENT_WEBSITE_ID_KEY => $this->getDefaultWebsiteId()]
        );
        $this->assertItemsCount(3);
    }

    public function testRenameIndex()
    {
        $this->loadFixtures([LoadProductsToIndex::class]);

        $restrictedProduct = $this->getReference(LoadProductsToIndex::REFERENCE_PRODUCT1);

        $this->setClassSupportedExpectation(TestProduct::class, true);
        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex([TestProduct::class]);
        $this->assertItemsCount(2);

        $this->dispatcher->addListener(
            $this->getRestrictEntityEventName(),
            function (RestrictIndexEntityEvent $event) use ($restrictedProduct) {
                $qb = $event->getQueryBuilder();
                list($rootAlias) = $qb->getRootAliases();
                $qb->where($qb->expr()->neq($rootAlias . '.id', ':id'))
                    ->setParameter('id', $restrictedProduct->getId());
            },
            -255
        );

        $this->indexer->reindex([TestProduct::class]);
        $this->assertItemsCount(1);
    }

    private function prepareDataWithAllWebsitesWithReindex()
    {
        $this->loadFixtures([LoadOtherWebsite::class]);
        $this->prepareDataWithReindex();
    }

    private function prepareDataWithReindex()
    {
        $this->loadFixtures([LoadProductsToIndex::class]);

        $this->setClassSupportedExpectation(TestProduct::class, true);
        $this->setListener();

        $this->setEntityAliasExpectation();
        $this->setGetEntityConfigExpectation();

        $this->indexer->reindex(TestProduct::class);
    }
}
