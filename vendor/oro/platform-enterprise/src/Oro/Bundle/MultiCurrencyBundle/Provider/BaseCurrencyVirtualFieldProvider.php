<?php

namespace Oro\Bundle\MultiCurrencyBundle\Provider;

use Psr\Log\LoggerInterface;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\CurrencyBundle\DoctrineExtension\Dbal\Types\MoneyValueType;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;

use Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Tools\ConfigHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class BaseCurrencyVirtualFieldProvider implements VirtualFieldProviderInterface
{
    /** @var ConfigProvider */
    protected $multiCurrencyConfigProvider;

    /** @var CurrencyQueryBuilderTransformerInterface */
    protected $qbTransformer;

    /** @var DefaultCurrencyProviderInterface */
    protected $currencyProvider;

    /** @var CurrencyNameHelper */
    protected $currencyNameHelper;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var LoggerInterface */
    protected $logger;

    /** @var array */
    protected $virtualFields = [];

    /**
     * @param ConfigProvider                           $multiCurrencyConfigProvider
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     * @param DefaultCurrencyProviderInterface         $currencyProvider
     * @param CurrencyNameHelper                       $currencyNameHelper
     * @param TranslatorInterface                      $translator
     * @param LoggerInterface                          $logger
     */
    public function __construct(
        ConfigProvider $multiCurrencyConfigProvider,
        CurrencyQueryBuilderTransformerInterface $qbTransformer,
        DefaultCurrencyProviderInterface $currencyProvider,
        CurrencyNameHelper $currencyNameHelper,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->multiCurrencyConfigProvider = $multiCurrencyConfigProvider;
        $this->qbTransformer = $qbTransformer;
        $this->currencyProvider = $currencyProvider;
        $this->currencyNameHelper = $currencyNameHelper;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualFields($className)
    {
        $this->ensureVirtualFieldsInitialized($className);

        return isset($this->virtualFields[$className]) ? array_keys($this->virtualFields[$className]) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function isVirtualField($className, $fieldName)
    {
        $this->ensureVirtualFieldsInitialized($className);

        return isset($this->virtualFields[$className][$fieldName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualFieldQuery($className, $fieldName)
    {
        $this->ensureVirtualFieldsInitialized($className);

        return $this->virtualFields[$className][$fieldName]['query'];
    }

    /**
     * @param string $className
     */
    public function ensureVirtualFieldsInitialized($className)
    {
        if (isset($this->virtualFields[$className])) {
            return;
        }

        $this->virtualFields[$className] = [];
        if ($this->multiCurrencyConfigProvider->hasConfig($className)) {
            /** @var ConfigInterface[] $fields */
            $fields = $this->multiCurrencyConfigProvider->filter(
                function (ConfigInterface $config) {
                    /** @var FieldConfigId $fieldConfigId */
                    $fieldConfigId = $config->getId();
                    return
                        ExtendHelper::isFieldAccessible($config)
                        && $fieldConfigId->getFieldType() === MoneyValueType::TYPE;
                },
                $className
            );

            foreach ($fields as $field) {
                if (!$field->has('target') || !$field->has('virtual_field')) {
                    continue;
                }
                $target = $field->get('target');
                $virtualField = $field->get('virtual_field');

                $virtualFieldQuery = $this->qbTransformer->getTransformSelectQuery($target, null, 'entity');
                $this->virtualFields[$className][$virtualField] = [
                    'query' => [
                        'select' => [
                            'expr'         => $virtualFieldQuery,
                            'translatable' => false,
                            'label'        => $this->getVirtualFieldLabel($className, $target),
                            'return_type'  => 'money'
                        ]
                    ]
                ];
            }
        }
    }

    /**
     * Return translated field label
     *
     * @param string $className
     * @param string $virtualFieldName
     *
     * @return string
     */
    protected function getVirtualFieldLabel($className, $virtualFieldName)
    {
        $fieldLabel = ConfigHelper::getTranslationKey('entity', 'label', $className, $virtualFieldName);
        $signLabel = $this->currencyNameHelper->getCurrencyName($this->currencyProvider->getDefaultCurrency());

        return sprintf('%s (%s)', $this->translator->trans($fieldLabel), $signLabel);
    }
}
