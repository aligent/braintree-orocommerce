<?php

namespace Oro\Bundle\MultiCurrencyBundle\Form\Type;

use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiCurrencyBundle\Form\Transformer\ArrayToJsonTransformer;
use Oro\Bundle\CurrencyBundle\Provider\ViewTypeProviderInterface;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\OrganizationCurrency;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\CurrencyNotUsedInConfig;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\CurrencyNotUsedInEntities;

class CurrencyGridType extends AbstractType
{
    const TARGET_FORM_NAME = 'oro_currency___default_currency';
    const CONFIG_FORM_NAME = 'oro_multi_currency___allowed_currencies';
    const TARGET_FORM_ELEMENT_NAME = 'value';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var CurrencyNameHelper
     */
    protected $currencyNameHelper;

    /**
     * @var CurrencyNotUsedInEntities
     */
    protected $currencyNotUsedInEntitiesConstraint;

    public function __construct(
        ConfigManager $configManager,
        CurrencyNameHelper $currencyNameHelper
    ) {
        $this->configManager        = $configManager;
        $this->currencyNameHelper   = $currencyNameHelper;
        $this->currencyNotUsedInEntitiesConstraint = new CurrencyNotUsedInEntities();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ArrayToJsonTransformer());
        /**
         * Setting value help to catch currency removing
         */
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if (is_array($event->getData())) {
                $this->currencyNotUsedInEntitiesConstraint->setValue($event->getData());
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'rebuildFormElement']);
    }

    /**
     * Rebuild form element
     * "default_currency" for save dynamic data
     * from allowed_currencies
     *
     * @param FormEvent $formEvent
     *
     * @return bool | void
     */
    public function rebuildFormElement(FormEvent $formEvent)
    {
        /**
         * If transformation is failed then we use empty array as default value
         */
        $data = is_array($formEvent->getData()) ? $formEvent->getData() : [];

        $choices = array_intersect(
            array_flip(Intl::getCurrencyBundle()->getCurrencyNames()),
            $data
        );

        $form = $formEvent->getForm();

        /** Get form for "rebuild" element */
        $targetForm = $form->getRoot()->get(self::TARGET_FORM_NAME);

        FormUtils::replaceField(
            $targetForm,
            self::TARGET_FORM_ELEMENT_NAME,
            ['choices' => $choices],
            ['choice_list']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['currencyCollection'] = $this->getCurrencyCollection($options);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'restrict' => false,
            'constraints' => [
                new OrganizationCurrency(),
                $this->currencyNotUsedInEntitiesConstraint,
                new CurrencyNotUsedInConfig()
            ]
        ]);

        $resolver->setAllowedTypes([
            'restrict' => 'bool'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\HiddenType';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_currency_grid';
    }

    /**
     * @param array $options
     * @return bool
     */
    protected function isRestricted(array $options)
    {
        return (isset($options['restrict'])
            && $options['restrict']
            && $this->configManager->get(
                MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_ALLOWED_CURRENCIES)
            )
        );
    }

    /**
     * @param array $options
     * @return array|\string[]
     */
    protected function getCurrencyCollection(array $options)
    {
        $currencyCollection = $this->currencyNameHelper->getCurrencyFilteredList();
        if ($this->isRestricted($options)) {
            $allowedCurrencies = $this->configManager->get(
                MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_ALLOWED_CURRENCIES)
            );
            $currencyCollection = array_intersect_key(
                $currencyCollection,
                array_flip($allowedCurrencies)
            );
        }

        foreach ($currencyCollection as $currencyIsoCode => &$currencyProperties) {
            $currencyProperties = [
                'code' => $currencyIsoCode,
                'name' => $this->currencyNameHelper->getCurrencyName(
                    $currencyIsoCode,
                    ViewTypeProviderInterface::VIEW_TYPE_NAME
                ),
                'symbol' => $this->currencyNameHelper->getCurrencyName(
                    $currencyIsoCode,
                    ViewTypeProviderInterface::VIEW_TYPE_SYMBOL
                )
            ];
        }

        unset($currencyProperties);
        return $currencyCollection;
    }
}
