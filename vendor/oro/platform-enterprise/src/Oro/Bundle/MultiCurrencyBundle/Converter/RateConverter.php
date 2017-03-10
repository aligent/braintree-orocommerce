<?php

namespace Oro\Bundle\MultiCurrencyBundle\Converter;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\MultiCurrencyBundle\Provider\RateProvider;
use Oro\Bundle\CurrencyBundle\Converter\RateConverterInterface;

class RateConverter implements RateConverterInterface
{
    /** @var RateProvider */
    protected $rateProvider;

    /** @var DefaultCurrencyProviderInterface */
    protected $defaultCurrencyProvider;

    /**
     * @param RateProvider $rateProvider
     * @param DefaultCurrencyProviderInterface $defaultCurrencyProvider
     */
    public function __construct(RateProvider $rateProvider, DefaultCurrencyProviderInterface $defaultCurrencyProvider)
    {
        $this->rateProvider = $rateProvider;
        $this->defaultCurrencyProvider = $defaultCurrencyProvider;
    }

    /**
     * Returns amount base currency
     * @param MultiCurrency $currency
     *
     * @return float
     */
    public function getBaseCurrencyAmount(MultiCurrency $currency)
    {
        if (null !== $currency->getBaseCurrencyValue()) {
            return $currency->getBaseCurrencyValue();
        }

        $baseCurrencyCode = $this->defaultCurrencyProvider->getDefaultCurrency();
        $currencyCode = $currency->getCurrency();

        if ($currencyCode === $baseCurrencyCode) {
            $baseCurrencyAmount = $currency->getValue();
        } else {
            $rate = $this->rateProvider->getRate($currencyCode);
            $baseCurrencyAmount = $currency->getValue() * $rate;
        }

        return $baseCurrencyAmount;
    }
}
