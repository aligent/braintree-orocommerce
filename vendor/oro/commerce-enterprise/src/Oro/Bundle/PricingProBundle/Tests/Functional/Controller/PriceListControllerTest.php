<?php

namespace Oro\Bundle\PricingProBundle\Tests\Functional\Controller;

use Symfony\Component\Intl\Intl;

use Oro\Bundle\PricingBundle\Tests\Functional\Controller\PriceListControllerTest
    as PriceListControllerCommunityTest;

/**
 * @dbIsolation
 */
class PriceListControllerTest extends PriceListControllerCommunityTest
{
    protected function checkCurrenciesOnPage($crawler)
    {
        $this->assertContains(Intl::getCurrencyBundle()->getCurrencyName(static::CURRENCY), $crawler->html());
    }
}
