<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Converter;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\CurrencyBundle\Provider\CurrencyProviderInterface;
use Oro\Bundle\MultiCurrencyBundle\Converter\RateConverter;

class RateConverterTest extends \PHPUnit_Framework_TestCase
{
    const USD_TO_EUR_CONVERSION_RATE = 0.9;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rateProvider;

    /** @var  RateConverter */
    protected $rateConverter;

    public function setUp()
    {
        $this->rateProvider = $this
            ->getMockBuilder('Oro\Bundle\MultiCurrencyBundle\Provider\RateProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $currencyProvider = $this
            ->getMockBuilder(CurrencyProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $currencyProvider
            ->expects($this->any())
            ->method('getDefaultCurrency')
            ->willReturn('USD');

        $this->rateConverter = new RateConverter($this->rateProvider, $currencyProvider);
    }

    public function testConvertForDefaultCurrency()
    {
        $multiCurrency = MultiCurrency::create(10, 'USD');
        $this->assertSame(10, $this->rateConverter->getBaseCurrencyAmount($multiCurrency));
    }

    public function testConvertForCustomCurrency()
    {
        $multiCurrency = MultiCurrency::create(10, 'EUR');

        $this->rateProvider
            ->expects($this->once())
            ->method('getRate')
            ->with('EUR')
            ->willReturn(self::USD_TO_EUR_CONVERSION_RATE);

        $this->assertSame(
            10 * self::USD_TO_EUR_CONVERSION_RATE,
            $this->rateConverter->getBaseCurrencyAmount($multiCurrency)
        );
    }

    public function testConvertForFixedRate()
    {
        $multiCurrency = MultiCurrency::create(10, 'EUR', 7);
        $this->assertSame(7, $this->rateConverter->getBaseCurrencyAmount($multiCurrency));
    }
}
