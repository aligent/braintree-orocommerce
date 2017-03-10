<?php

namespace Oro\Bundle\SalesOrderCurrencyBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrders as CommunityLoadOrders;
use Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderUsers;
use Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadPaymentTermData;

class LoadOrders extends CommunityLoadOrders
{
    const ORDER_USD = 'order_in_usd';
    const ORDER_EUR = 'order_in_eur';

    const SUBTOTAL = '150';
    const TOTAL = '400';

    /**
     * @var array
     */
    protected $orders = [
        self::ORDER_USD => [
            'user' => LoadOrderUsers::ORDER_USER_1,
            'customerUser' => LoadCustomerUserData::AUTH_USER,
            'poNumber' => 'PO_NUM_USD',
            'currency' => 'USD',
            'subtotal' => self::SUBTOTAL,
            'total' => self::TOTAL,
            'paymentTerm' => LoadPaymentTermData::PAYMENT_TERM_NET_10,
        ],
        self::ORDER_EUR => [
            'user' => LoadOrderUsers::ORDER_USER_1,
            'customerUser' => LoadCustomerUserData::AUTH_USER,
            'poNumber' => 'PO_NUM_EUR',
            'currency' => 'EUR',
            'subtotal' => self::SUBTOTAL,
            'total' => self::TOTAL,
            'paymentTerm' => LoadPaymentTermData::PAYMENT_TERM_NET_10
        ],
    ];
}
