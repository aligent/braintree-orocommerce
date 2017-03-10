<?php

namespace Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\Controller\Grid;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures\LoadMulticurrencyConfig;
use Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures\LoadOrders;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;

/**
 * @dbIsolation
 */
class OrderGridControllerTest extends AbstractOrderGridController
{
    /** @var int */
    protected $customerUserId;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadMulticurrencyConfig::class,
                LoadOrders::class
            ]
        );
        $this->initCustomerUserId();
    }

    public function testRequiredFieldsExistInAdminOrder()
    {
        $response = $this->client->requestGrid('orders-grid', [], true);
        $result = self::getJsonResponseContent($response, 200);
        $this->checkResultOnRequiredFieldsExistInOrder($result);
    }

    /**
     * @depends testRequiredFieldsExistInAdminOrder
     * @dataProvider adminOrderParamProvider
     */
    public function testAdminOrderGridSorterAndFilter($gridParams, $expectedResult)
    {
        $response = $this->client->requestGrid('orders-grid', $gridParams, true);
        $result = self::getJsonResponseContent($response, 200);

        $orderIdentifiers = array_map(function ($order) {
            return $order['identifier'];
        }, $result['data']);

        $this->assertEquals(
            $expectedResult,
            $orderIdentifiers
        );
    }

    public function testRequiredFieldsExistInAdminCustomerOrderGrid()
    {
        $response = $this->client->requestGrid([
            'gridName' => 'customer-orders-grid',
            'customer-orders-grid[customer_id]' => $this->customerUserId,
        ], [], true);
        $result = self::getJsonResponseContent($response, 200);
        $this->checkResultOnRequiredFieldsExistInOrder($result);
    }

    /**
     * @depends testRequiredFieldsExistInAdminCustomerOrderGrid
     * @dataProvider adminCustomerOrderParamProvider
     */
    public function testAdminCustomerOrderGridSorterAndFilter($gridParams, $expectedResult)
    {
        $response = $this->client->requestGrid(
            [
                'gridName' => 'customer-orders-grid',
                'customer-orders-grid[customer_id]' => $this->customerUserId,
            ],
            $gridParams,
            true
        );
        $result = self::getJsonResponseContent($response, 200);

        $orderIdentifiers = array_map(function ($order) {
            return $order['identifier'];
        }, $result['data']);

        $this->assertEquals(
            $expectedResult,
            $orderIdentifiers
        );
    }

    public function adminOrderParamProvider()
    {
        return $this->getParamArray('orders-grid');
    }

    public function adminCustomerOrderParamProvider()
    {
        return $this->getParamArray('customer-orders-grid');
    }

    protected function initCustomerUserId()
    {
        $manager = $this->client->getContainer()->get('doctrine')->getManagerForClass(
            'OroCustomerBundle:CustomerUser'
        );
        /** @var CustomerUser $customerUser */
        $customerUser = $manager->getRepository('OroCustomerBundle:CustomerUser')->findOneBy(
            ['username' => LoadCustomerUserData::AUTH_USER]
        );
        $this->customerUserId = $customerUser->getId();
    }
}
