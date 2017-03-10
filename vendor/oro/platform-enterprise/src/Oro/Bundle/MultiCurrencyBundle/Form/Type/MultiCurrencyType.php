<?php

namespace Oro\Bundle\MultiCurrencyBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Form\Type\MultiCurrencyType as BaseType;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Oro\Bundle\MultiCurrencyBundle\Provider\RateProvider;

class MultiCurrencyType extends BaseType
{
    /**
     * @var RateProvider
     */
    protected $rateProvider;

    /**
     * @var DefaultCurrencyProviderInterface
     */
    protected $currencyConfig;

    /**
     * @var CurrencyNameHelper
     */
    protected $currencyNameHelper;

    /**
     * MultiCurrencyType constructor.
     * @param RoundingServiceInterface $roundingService
     * @param RateProvider $rateProvider
     * @param DefaultCurrencyProviderInterface $currencyConfig
     * @param CurrencyNameHelper $currencyNameHelper
     */
    public function __construct(
        RoundingServiceInterface    $roundingService,
        RateProvider                $rateProvider,
        DefaultCurrencyProviderInterface     $currencyConfig,
        CurrencyNameHelper          $currencyNameHelper
    ) {
        parent::__construct($roundingService);

        $this->rateProvider       = $rateProvider;
        $this->currencyConfig     = $currencyConfig;
        $this->currencyNameHelper = $currencyNameHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['defaultCurrency'] = $this->currencyNameHelper->getCurrencyName(
            $this->currencyConfig->getDefaultCurrency()
        );
        $view->vars['currencyRates'] = $this->rateProvider->getCurrentOrganizationRateList();
    }
}
