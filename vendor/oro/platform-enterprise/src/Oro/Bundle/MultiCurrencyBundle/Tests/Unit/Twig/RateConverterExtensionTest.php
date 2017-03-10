<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Twig;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\MultiCurrencyBundle\Twig\RateConverterExtension;

class RateConverterExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RateConverterExtension
     */
    protected $extension;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper */
        $currencyNameHelper = $this
            ->getMockBuilder('Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper')
            ->disableOriginalConstructor()
            ->setMethods(['formatPrice'])
            ->getMock();

        $currencyNameHelper
            ->expects($this->any())
            ->method('formatPrice')
            ->willReturn('12 USD');

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Oro\Bundle\CurrencyBundle\Converter\RateConverterInterface */
        $rateConverter = $this
            ->getMockBuilder('Oro\Bundle\CurrencyBundle\Converter\RateConverterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrencyAmount'])
            ->getMock();

        $rateConverter
            ->expects($this->any())
            ->method('getBaseCurrencyAmount')
            ->willReturn('12');

        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultCurrencyProviderInterface */
        $defaultCurrencyProvider = $this
            ->getMockBuilder(DefaultCurrencyProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultCurrency'])
            ->getMockForAbstractClass();

        $defaultCurrencyProvider
            ->expects($this->any())
            ->method('getDefaultCurrency')
            ->willReturn('EUR');

        $this->extension = new RateConverterExtension($rateConverter, $currencyNameHelper, $defaultCurrencyProvider);
    }

    public function testGetFunctions()
    {
        /** @var \Twig_SimpleFunction[] $functions */
        $functions = $this->extension->getFunctions();

        $this->assertCount(1, $functions);

        $availableFunctions = ['oro_multicurrency_rate_converter'];

        foreach ($functions as $function) {
            $this->assertInstanceOf('Twig_SimpleFunction', $function);
            $this->assertTrue(in_array($function->getName(), $availableFunctions, true));
        }
    }

    /**
     * @dataProvider convertDataProvider
     * @param MultiCurrency $currency
     * @param string $expected
     */
    public function testConvert(MultiCurrency $currency, $expected)
    {
        $this->assertEquals($expected, $this->extension->convert($currency));
    }

    /**
     * @return array
     */
    public function convertDataProvider()
    {
        return [
            [MultiCurrency::create(null, 'USD'), ''],
            [MultiCurrency::create(12, 'USD'), '12 USD'],
            [MultiCurrency::create(12, 'EUR'), '']
        ];
    }

    public function testGetName()
    {
        $this->assertEquals('oro_multicurrency', $this->extension->getName());
    }
}
