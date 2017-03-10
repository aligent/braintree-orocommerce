<?php

namespace Oro\Bundle\PricingProBundle\Tests\Unit\Manager;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\PricingProBundle\DependencyInjection\Configuration;
use Oro\Bundle\PricingProBundle\Manager\DisplayedCurrenciesConfigProvider;

class DisplayedCurrenciesConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var DisplayedCurrenciesConfigProvider
     */
    protected $provider;

    public function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()->getMock();

        $this->provider = new DisplayedCurrenciesConfigProvider($this->configManager);
    }

    public function testGetCurrencyList()
    {
        $currencyList = ['USD', 'EUR'];

        $this->configManager->expects(static::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::ENABLED_CURRENCIES))
            ->willReturn($currencyList);

        static::assertSame($currencyList, $this->provider->getCurrencyList());
    }

    public function testGetDefaultCurrency()
    {
        $currencyList = 'USD';

        $this->configManager->expects(static::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::DEFAULT_CURRENCY))
            ->willReturn($currencyList);

        static::assertSame($currencyList, $this->provider->getDefaultCurrency());
    }
}
