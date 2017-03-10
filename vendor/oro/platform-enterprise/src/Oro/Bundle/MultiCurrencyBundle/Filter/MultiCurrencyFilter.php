<?php

namespace Oro\Bundle\MultiCurrencyBundle\Filter;

use Oro\Bundle\CurrencyBundle\Provider\ViewTypeProviderInterface;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\DictionaryFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class MultiCurrencyFilter extends DictionaryFilter
{
    /** @var CurrencyNameHelper  */
    protected $currencyNameHelper;

    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        CurrencyNameHelper $currencyNameHelper
    ) {
        $this->currencyNameHelper = $currencyNameHelper;
        parent::__construct($factory, $util);
    }

    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $dictionaryChoiceCollection = [];
        $currencyChoices = $this->currencyNameHelper->getCurrencyChoices(
            ViewTypeProviderInterface::VIEW_TYPE_FULL_NAME
        );

        foreach ($currencyChoices as $currencyCode => $currencyLabel) {
            $dictionaryChoiceCollection[] = [
                'id' => $currencyCode,
                'value' => $currencyCode,
                'text' => $currencyLabel
            ];
        }

        $metadata['class'] = '';
        $metadata['select2ConfigData'] = $dictionaryChoiceCollection;

        return $metadata;
    }

    protected function getFilteredFieldName(FilterDatasourceAdapterInterface $ds)
    {
        return $this->get(FilterUtility::DATA_NAME_KEY);
    }
}
