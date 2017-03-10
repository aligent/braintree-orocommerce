<?php

namespace Oro\Bundle\MultiCurrencyBundle\Twig;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\CurrencyBundle\Converter\RateConverterInterface;

class RateConverterExtension extends \Twig_Extension
{
    /**
     * @var RateConverterInterface
     */
    protected $converter;

    /**
     * @var DefaultCurrencyProviderInterface
     */
    protected $defaultCurrencyProvider;

    /**
     * @var CurrencyNameHelper;
     */
    protected $helper;

    /**
     * @param RateConverter $converter
     * @param CurrencyNameHelper $helper
     * @param DefaultCurrencyProviderInterface $defaultCurrencyProvider
     */
    public function __construct(
        RateConverterInterface $converter,
        CurrencyNameHelper $helper,
        DefaultCurrencyProviderInterface $defaultCurrencyProvider
    ) {
        $this->converter = $converter;
        $this->defaultCurrencyProvider = $defaultCurrencyProvider;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('oro_multicurrency_rate_converter', [$this, 'convert']),
        ];
    }

    /**
     * @param MultiCurrency $multiCurrency
     * @return string
     */
    public function convert(MultiCurrency $multiCurrency)
    {
        if (null === $multiCurrency->getValue()) {
            return '';
        }

        $defaultCurrency = $this->defaultCurrencyProvider->getDefaultCurrency();

        if ($defaultCurrency === $multiCurrency->getCurrency()) {
            return '';
        }

        $value = $this->converter->getBaseCurrencyAmount($multiCurrency);

        return $this->helper->formatPrice(Price::create($value, $defaultCurrency));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_multicurrency';
    }
}
