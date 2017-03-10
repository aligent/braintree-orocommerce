<?php

namespace Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\Controller;

use Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures\LoadMulticurrencyConfig;
use Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures\LoadOrders;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;

/**
 * @dbIsolation
 */
class OrderFrontendGridControllerTest extends AbstractOrderGridController
{
    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );
        $this->loadFixtures(
            [
                LoadMulticurrencyConfig::class,
                LoadOrders::class
            ]
        );
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_order_frontend_index'));
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @depends testIndex
     */
    public function testRequiredFieldExistInFrontendOrderGrid()
    {
        $response = $this->client->requestFrontendGrid('frontend-orders-grid');
        $result = self::getJsonResponseContent($response, 200);
        $this->checkResultOnRequiredFieldsExistInOrder($result);
    }

    /**
     * @depends testRequiredFieldExistInFrontendOrderGrid
     * @dataProvider paramProvider
     */
    public function testSorterAndFilter($gridParams, $expectedResult)
    {
        $response = $this->client->requestFrontendGrid('frontend-orders-grid', $gridParams);
        $result = self::getJsonResponseContent($response, 200);

        $orderIdentifiers = array_map(function ($order) {
            return $order['identifier'];
        }, $result['data']);

        $this->assertEquals(
            $expectedResult,
            $orderIdentifiers
        );
    }

    public function paramProvider()
    {
        return $this->getParamArray('frontend-orders-grid');
    }
}
