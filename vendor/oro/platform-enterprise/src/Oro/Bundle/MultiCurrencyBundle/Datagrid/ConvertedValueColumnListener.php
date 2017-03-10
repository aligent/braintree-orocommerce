<?php

namespace Oro\Bundle\MultiCurrencyBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\CurrencyBundle\Datagrid\InlineEditing\InlineEditColumnOptions\MultiCurrencyGuesser as Guesser;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;

class ConvertedValueColumnListener
{
    /** @var array  */
    protected $baseCurrencyValueColumns = [];

    /** @var array Contains list of table aliases for multi-currency holders (entities)*/
    protected $columnToTableAliasMapping = [];

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    /** @var EntityClassResolver */
    protected $entityClassResolver;

    /** @var  string */
    protected $rootEntityAlias;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DefaultCurrencyProviderInterface */
    protected $currencyProvider;

    /** @var CurrencyNameHelper */
    protected $currencyNameHelper;

    /**
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     * @param EntityClassResolver $entityClassResolver
     * @param TranslatorInterface $translator
     * @param DefaultCurrencyProviderInterface $defaultCurrencyProvider
     * @param CurrencyNameHelper $currencyNameHelper
     */
    public function __construct(
        CurrencyQueryBuilderTransformerInterface $qbTransformer,
        EntityClassResolver $entityClassResolver,
        TranslatorInterface $translator,
        DefaultCurrencyProviderInterface $defaultCurrencyProvider,
        CurrencyNameHelper $currencyNameHelper
    ) {
        $this->qbTransformer = $qbTransformer;
        $this->entityClassResolver = $entityClassResolver;
        $this->translator = $translator;
        $this->currencyProvider = $defaultCurrencyProvider;
        $this->currencyNameHelper = $currencyNameHelper;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        if (!$this->isApplicable($config)) {
            return;
        }

        $this->baseCurrencyValueColumns = [];
        $columns = $config->offsetGetByPath('[columns]', []);
        $newColumnsSet = $this->getNewColumnsSet($columns);
        if (!empty($this->baseCurrencyValueColumns)) {
            $config->offsetSetByPath('[columns]', $newColumnsSet);

            $baseCurrencyValueColumns = array_intersect($this->baseCurrencyValueColumns, array_keys($columns));

            // add workflow step if it must be shown and there are no workflow step columns
            if (empty($baseCurrencyValueColumns)) {
                $this->addFiltersAndSorters($config);
            }
        }
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        if ($event->getDatagrid()->getConfig()->isOrmDatasource()) {
            /** @var QueryBuilder $qb */
            $qb = $event->getDatagrid()->getDatasource()->getQueryBuilder();
            foreach ($this->baseCurrencyValueColumns as $columnName => $newColumnName) {
                $query = $this->qbTransformer->getTransformSelectQuery(
                    $columnName,
                    $qb,
                    $this->columnToTableAliasMapping[$columnName],
                    $newColumnName
                );
                $qb->addSelect($query);
            }
        }
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    protected function getNewColumnsSet(array &$columns)
    {
        $newColumnsSet = [];
        foreach ($columns as $columnName => $columnConfig) {
            $newColumnsSet[$columnName] = $columnConfig;
            if (isset($columnConfig[PropertyInterface::FRONTEND_TYPE_KEY])
                && $columnConfig[PropertyInterface::FRONTEND_TYPE_KEY] === Guesser::MULTI_CURRENCY_TYPE
            ) {
                $multiCurrencyConfig = $this->prepareMultiCurrencyConfigOptions($columnConfig);
                $entityAlias = $multiCurrencyConfig['entity_alias'] ?: $this->rootEntityAlias;


                $newColumnName = sprintf('%s%s', $columnName, 'BaseCurrency');
                $newColumnsSet[$newColumnName] = [
                    'frontend_type' => 'currency',
                    'translatable' => false,
                    'label' => $multiCurrencyConfig['base_currency_label']
                ];

                if (isset($columnConfig['renderable'])) {
                    $newColumnsSet[$newColumnName]['renderable'] = $columnConfig['renderable'];
                }

                $this->baseCurrencyValueColumns[$columnName] = $newColumnName;
                $this->columnToTableAliasMapping[$columnName] = $entityAlias;
            }
        }

        return $newColumnsSet;
    }

    /**
     * @param $columnConfig
     *
     * @return array
     */
    protected function prepareMultiCurrencyConfigOptions($columnConfig)
    {
        $defaultConfigOptions = [
            'base_currency_label' => null,
            'entity_alias'        => null
        ];

        $multiCurrencyConfig = isset($columnConfig[Guesser::MULTI_CURRENCY_CONFIG])
            ? $columnConfig[Guesser::MULTI_CURRENCY_CONFIG]
            : [];

        $multiCurrencyConfig = array_merge($defaultConfigOptions, $multiCurrencyConfig);

        $signLabel = $this->currencyNameHelper->getCurrencyName($this->currencyProvider->getDefaultCurrency());
        $baseFieldLabel = $this->translator->trans($columnConfig['label']);
        $multiCurrencyConfig['base_currency_label'] = sprintf('%s (%s)', $baseFieldLabel, $signLabel);

        return $multiCurrencyConfig;
    }

    /**
     * @param DatagridConfiguration $config
     *
     * @return bool
     */
    protected function isApplicable(DatagridConfiguration $config)
    {
        // datasource type other than ORM is not supported yet
        if (!$config->isOrmDatasource()) {
            return false;
        }

        // get root entity
        $rootEntity = $config->getOrmQuery()->getRootEntity($this->entityClassResolver);
        if (!$rootEntity) {
            return false;
        }
        $this->rootEntityAlias = $config->getOrmQuery()->getRootAlias();
        if (!$this->rootEntityAlias) {
            return false;
        }

        return true;
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addFiltersAndSorters(DatagridConfiguration $config)
    {
        // add sorter (only if there is at least one sorter)
        $columns = $config->offsetGetByPath('[columns]', []);
        $sorters = $config->offsetGetByPath('[sorters][columns]', []);
        foreach ($this->baseCurrencyValueColumns as $columnName => $newColumnName) {
            if (!isset($columns[$columnName][Guesser::MULTI_CURRENCY_CONFIG]['sortable'])
            || $columns[$columnName][Guesser::MULTI_CURRENCY_CONFIG]['sortable'] !== false) {
                $sorters[$newColumnName] = ['data_name' => $newColumnName];
            }
        }
        $config->offsetSetByPath('[sorters][columns]', $sorters);

        $filters = $config->offsetGetByPath('[filters][columns]', []);
        foreach ($this->baseCurrencyValueColumns as $columnName => $newColumnName) {
            $filters[$newColumnName] = [
                'data_name' => $newColumnName,
                'type' => 'currency'
            ];

            if (isset($filters[$columnName]['enabled'])) {
                $filters[$newColumnName]['enabled'] = $filters[$columnName]['enabled'];
            }
        }
        $config->offsetSetByPath('[filters][columns]', $filters);
    }
}
