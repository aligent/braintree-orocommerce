<?php

namespace Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures\LoadOrders;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures\LoadMulticurrencyConfig;
use Oro\Bundle\OrderBundle\Entity\OrderDiscount;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductUnitPrecisions;

/**
 * @dbIsolation
 */
class OrderControllerTest extends WebTestCase
{
    const ORDER_PO_NUMBER = 'PO_NUMBER';
    const NON_BASE_ORDER_CURRENCY = 'EUR';
    const PRODUCT_QTY = 10;
    const PRODUCT_PRICE = 100;
    const TOTAL_VALUE = 1000;

    protected function setUp()
    {
        $this->initClient([], array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1]));
        $this->client->useHashNavigation(true);

        $this->loadFixtures(
            [
                LoadMulticurrencyConfig::class,
                LoadOrders::class,
                LoadCustomerUserData::class,
                LoadProductUnitPrecisions::class
            ]
        );
    }

    public function testOrderInBaseCurrency()
    {
        /**
         * @var $orderInBaseCurrency Order
         */
        $orderInBaseCurrency = $this->getReference(LoadOrders::ORDER_USD);
        $crawler = $this->client->request('GET', $this->getUrl('oro_order_view', [
            'id' => $orderInBaseCurrency->getId()
        ]));

        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $subtotalInBaseCurrencyWrapper = $crawler
            ->filter('.responsive-section')
            ->eq(0)
            ->filter('.base-currency-wrapper')
        ;

        $this->assertEquals(
            $subtotalInBaseCurrencyWrapper->count(),
            0,
            'Incorrect behaviour, subtotal in base currency shouldn\'t displayed !'
        );
    }

    public function testOrderInNonBaseCurrency()
    {
        /**
         * @var $orderInBaseCurrency Order
         */
        $orderInNonBaseCurrency = $this->getReference(LoadOrders::ORDER_EUR);
        $crawler = $this->client->request('GET', $this->getUrl('oro_order_view', [
            'id' => $orderInNonBaseCurrency->getId()
        ]));

        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $subtotalInBaseCurrencyWrapper = $crawler
            ->filter('.responsive-section')
            ->eq(0)
            ->filter('.base-currency-wrapper')
        ;

        $this->assertEquals(
            $subtotalInBaseCurrencyWrapper->count(),
            1,
            'Incorrect behaviour, subtotal in base currency should be displayed !'
        );

        $subtotalInBaseCurrencyValue = $subtotalInBaseCurrencyWrapper->filter('.control-label')->eq(1)->text();

        $numberFormatter = $this->getContainer()->get('oro_locale.formatter.number');
        $subtotalInBaseCurrencyFormatted = $numberFormatter->formatCurrency(
            $orderInNonBaseCurrency->getSubtotal() * LoadMulticurrencyConfig::RATE_FROM_EUR
        );

        $this->assertEquals(
            $subtotalInBaseCurrencyValue,
            $subtotalInBaseCurrencyFormatted
        );
    }

    /**
     * @return Order
     */
    public function testNewOrderInNonBaseCurrency()
    {
        $customerId = $this->getReference('customer.level_1')->getId();
        $ownerId = $this->getContainer()->get('oro_security.security_facade')->getLoggedUser()->getId();
        $websiteId = $this->getContainer()->get('oro_website.manager')->getDefaultWebsite()->getId();

        $crawler = $this->client->request('GET', $this->getUrl('oro_order_create'));
        $result  = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();

        $submittedData = [
            'input_action' => 'save_and_stay',
            'oro_order_type' => [
                '_token' => $form['oro_order_type[_token]']->getValue(),
                'customer' => $customerId,
                'owner'   => $ownerId,
                'poNumber' => self::ORDER_PO_NUMBER,
                'website'  => $websiteId,
                'currency' => self::NON_BASE_ORDER_CURRENCY,
                'lineItems' => [
                    [
                        'product' => $this->getReference('product-1')->getId(),
                        'quantity' => self::PRODUCT_QTY,
                        'productUnit' => 'liter',
                        'price' => [
                            'value' => self::PRODUCT_PRICE,
                            'currency' => 'EUR'
                        ],
                        'priceType' => OrderLineItem::PRICE_TYPE_UNIT,
                    ],
                ]
            ]
        ];

        $this->client->followRedirects(true);

        $this->client->request(
            $form->getMethod(),
            $form->getUri(),
            $submittedData,
            $form->getPhpFiles()
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, 200);

        /** @var Order $order */
        $order = $this->getContainer()->get('doctrine')
            ->getManagerForClass('OroOrderBundle:Order')
            ->getRepository('OroOrderBundle:Order')
            ->findOneBy(['poNumber' => self::ORDER_PO_NUMBER]);

        $this->assertNotEmpty($order);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $amount = sprintf("%.4f", self::TOTAL_VALUE);
        $amountInBaseCurrency = sprintf(
            "%.4f",
            self::TOTAL_VALUE * LoadMulticurrencyConfig::RATE_FROM_EUR
        );

        $orderTotals = [
            'total_object' => MultiCurrency::create($amount, self::NON_BASE_ORDER_CURRENCY, $amountInBaseCurrency),
            'subtotal_object' => MultiCurrency::create($amount, self::NON_BASE_ORDER_CURRENCY, $amountInBaseCurrency)
        ];

        foreach ($orderTotals as $fieldName => $fieldValue) {
            $this->assertEquals(
                $propertyAccessor->getValue($order, $fieldName),
                $fieldValue,
                sprintf('Multicurrency object %s contains incorrect values', $fieldName)
            );
        }

        return $order;
    }

    /**
     * @depends testNewOrderInNonBaseCurrency
     * @param Order $order
     * @return int $id
     **/
    public function testFreezeAmounInBaseCurrency(Order $order)
    {
        /**
         * Emulate freeze of total in base currency
         */
        $order->setBaseTotalValue(self::TOTAL_VALUE);
        $order->setBaseSubtotalValue(self::TOTAL_VALUE);
        $this->getContainer()->get('doctrine')->getManager()->flush($order);

        $crawler = $this->client->request('GET', $this->getUrl('oro_order_view', [
            'id' => $order->getId()
        ]));

        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $subtotalInBaseCurrencyWrapper = $crawler
            ->filter('.responsive-section')
            ->eq(0)
            ->filter('.base-currency-wrapper')
        ;

        $this->assertEquals(
            $subtotalInBaseCurrencyWrapper->count(),
            1,
            'Incorrect behaviour, subtotal in base currency should be displayed !'
        );

        $subtotalInBaseCurrencyValue = $subtotalInBaseCurrencyWrapper->filter('.control-label')->eq(1)->text();

        $numberFormatter = $this->getContainer()->get('oro_locale.formatter.number');
        $subtotalInBaseCurrencyFormatted = $numberFormatter->formatCurrency(self::TOTAL_VALUE);

        $this->assertEquals(
            $subtotalInBaseCurrencyValue,
            $subtotalInBaseCurrencyFormatted
        );

        $totalBlock = $crawler->filterXPath('//div[@class="totals-container"]/parent::div');
        $this->assertEquals($totalBlock->count(), 1, 'Total components missed !');

        $totalOptionsDecoded = $totalBlock->getNode(0)->getAttribute('data-page-component-options');
        $this->assertNotEmpty($totalOptionsDecoded);

        $totalOptions = json_decode($totalOptionsDecoded, true);
        $this->assertTotalsEqual($totalOptions, self::TOTAL_VALUE, self::TOTAL_VALUE);

        return $order->getId();
    }

    /**
     * @depends testFreezeAmounInBaseCurrency
     * @param int $id
     * @dataProvider updateDataProvider
     */
    public function testUpdateOrderInNonBaseCurrency($updateDataCallback, $expectedTotal, $expectedSubtotal, $id)
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_order_update', ['id' => $id]));

        /* @var $form Form */
        $form = $crawler->selectButton('Save')->form();

        $this->client->request(
            $form->getMethod(),
            $this->getUrl('oro_order_entry_point', ['id' => $id]),
            $updateDataCallback($form->getPhpValues()),
            $form->getPhpFiles()
        );

        $result  = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);

        $totalOptions = json_decode($result->getContent(), true);
        $this->assertTotalsEqual($totalOptions, $expectedTotal, $expectedSubtotal);
    }

    public function updateDataProvider()
    {
        return [
            'Item quantity changed' => [
                'updateDataCallback' => function ($formValues) {
                    $formValues['oro_order_type']['lineItems'][0]['quantity'] = self::PRODUCT_QTY * 2;
                    return $formValues;
                },
                'expectedTotal' => 4000,
                'expectedSubtotal' => 4000
            ],
            'Discount applied' => [
                'updateDataCallback' => function ($formValues) {
                    $formValues['oro_order_type']['discounts'] = [
                        [
                            'value' => '500',
                            'percent' => '50',
                            'amount' => '500',
                            'type' => OrderDiscount::TYPE_PERCENT,
                            'description' => 'Half discount',
                        ],
                    ];
                    $formValues['oro_order_type']['discountSum'] = 500;

                    return $formValues;
                },
                'expectedTotal' => 2000,
                'expectedSubtotal' => 1000
            ],
            'Shipping method changed' => [
                'updateDataCallback' => function ($formValues) {
                    $formValues['oro_order_type']['overriddenShippingCostAmount'] = [
                        'value' => 100,
                        'currency' => self::NON_BASE_ORDER_CURRENCY
                    ];
                    return $formValues;
                },
                'expectedTotal' => 2000,
                'expectedSubtotal' => 2200
            ]
        ];
    }

    /**
     * @param $totalOptions
     * @param $totalValue
     * @param $subtotalValue
     */
    protected function assertTotalsEqual($totalOptions, $totalValue, $subtotalValue)
    {
        $this->assertEquals(
            $totalOptions['totals']['subtotals'][0]['data']['baseAmount'],
            $totalValue,
            'Incorrect value of subtotal in base currency !'
        );
        $this->assertEquals(
            $totalOptions['totals']['total']['data']['baseAmount'],
            $subtotalValue,
            'Incorrect value of total in base currency !'
        );
    }
}
