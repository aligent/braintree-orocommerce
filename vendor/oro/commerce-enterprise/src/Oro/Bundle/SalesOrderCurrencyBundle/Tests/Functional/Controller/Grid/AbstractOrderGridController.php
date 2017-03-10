<?php

namespace Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\Controller\Grid;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures\LoadMulticurrencyConfig;
use Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures\LoadOrders;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterTypeInterface;

abstract class AbstractOrderGridController extends WebTestCase
{
    protected function getParamArray($gridName)
    {
        $subtotalInEur = LoadOrders::SUBTOTAL * LoadMulticurrencyConfig::RATE_FROM_EUR;

        return [
            'With sort by base total' => [
                [
                    $gridName . '[_sort_by][totalBaseCurrency]' => 'DESC'
                ],
                [
                    LoadOrders::ORDER_EUR,
                    LoadOrders::ORDER_USD
                ]
            ],
            'With sort by base subtotal' => [
                [
                    $gridName . '[_sort_by][totalBaseCurrency]' => 'DESC'
                ],
                [
                    LoadOrders::ORDER_EUR,
                    LoadOrders::ORDER_USD
                ]
            ],
            'With filter by base total' => [
                [
                    $gridName . '[_filter][totalBaseCurrency][type]'  => NumberFilterTypeInterface::TYPE_EQUAL,
                    $gridName . '[_filter][totalBaseCurrency][value]' => LoadOrders::TOTAL,
                ],
                [
                    LoadOrders::ORDER_USD
                ]
            ],
            'With filter by base subtotal'=> [
                [
                    $gridName . '[_filter][subtotalBaseCurrency][type]'=> NumberFilterTypeInterface::TYPE_EQUAL,
                    $gridName . '[_filter][subtotalBaseCurrency][value]'=> $subtotalInEur
                ],
                [
                    LoadOrders::ORDER_EUR
                ]
            ],
        ];
    }

    /**
     * @param array $result
     */
    protected function checkResultOnRequiredFieldsExistInOrder($result)
    {
        $order = reset($result['data']);

        $this->assertTrue(
            isset($order['subtotalBaseCurrency']) && isset($order['totalBaseCurrency'])
        );
    }
}
