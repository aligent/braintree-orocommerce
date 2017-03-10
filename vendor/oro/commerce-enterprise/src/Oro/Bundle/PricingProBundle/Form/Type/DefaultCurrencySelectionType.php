<?php

namespace Oro\Bundle\PricingProBundle\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfiguration;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencySelectionType;
use Oro\Bundle\CurrencyBundle\Provider\CurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\PricingProBundle\DependencyInjection\Configuration;
use Oro\Bundle\PricingProBundle\DependencyInjection\OroPricingProExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class DefaultCurrencySelectionType extends CurrencySelectionType
{
    const NAME = 'oro_pricing_pro_default_currency_selection';

    const PARENT_FORM_NAME = 'pricing';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param CurrencyProviderInterface $currencyProvider
     * @param LocaleSettings $localeSettings
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param CurrencyNameHelper $nameHelper
     */
    public function __construct(
        CurrencyProviderInterface $currencyProvider,
        LocaleSettings $localeSettings,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        CurrencyNameHelper $nameHelper
    ) {
        parent::__construct($currencyProvider, $localeSettings, $nameHelper);
        $this->translator = $translator;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
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
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $rootForm = $form->getRoot();

        if (!$this->isSyncApplicable($rootForm)) {
            return;
        }
        $pricing = $this->requestStack->getCurrentRequest()->get(static::PARENT_FORM_NAME);
        $defaultCurrencyKey = $this->getDefaultCurrencyConfigViewKey();
        $defaultCurrency = $this->getDefaultCurrency($pricing[$defaultCurrencyKey]);
        $enabledCurrenciesKey = $this->getEnabledCurrenciesConfigViewKey();
        $enabledCurrencies = $this->getEnabledCurrencies($pricing[$enabledCurrenciesKey]);

        if (in_array($defaultCurrency, $enabledCurrencies, true)) {
            return;
        }
        $currencyName = Intl::getCurrencyBundle()
            ->getCurrencyName($defaultCurrency, $this->localeSettings->getLocale());

        $form->addError(new FormError(
            $this->translator->trans(
                'oro.pricing_pro.validators.is_not_enabled',
                ['%currency%' => $currencyName],
                'validators'
            )
        ));
    }

    /**
     * @param array $defaultCurrencyData
     * @return string
     */
    protected function getDefaultCurrency(array $defaultCurrencyData = [])
    {
        $defaultCurrency = '';
        if (isset($defaultCurrencyData['use_parent_scope_value'])) {
            $defaultCurrency = CurrencyConfiguration::DEFAULT_CURRENCY;
        } elseif (isset($defaultCurrencyData['value'])) {
            $defaultCurrency = $defaultCurrencyData['value'];
        }

        return $defaultCurrency;
    }

    /**
     * @param array $enabledCurrenciesData
     * @return array
     */
    protected function getEnabledCurrencies(array $enabledCurrenciesData)
    {
        $enabledCurrencies = [];
        if (isset($enabledCurrenciesData['use_parent_scope_value'])) {
            $enabledCurrencies = [CurrencyConfiguration::DEFAULT_CURRENCY];
        } elseif (isset($enabledCurrenciesData['value'])) {
            $enabledCurrencies = $enabledCurrenciesData['value'];
        }

        return $enabledCurrencies;
    }

    /**
     * @param FormInterface $rootForm
     * @return bool
     */
    protected function isSyncApplicable(FormInterface $rootForm)
    {
        return $rootForm
            && $rootForm->getName() === static::PARENT_FORM_NAME
            && $rootForm->has($this->getEnabledCurrenciesConfigViewKey());
    }

    /**
     * @return string
     */
    private function getDefaultCurrencyConfigViewKey()
    {
        return $this->getConfigViewKey(Configuration::DEFAULT_CURRENCY);
    }

    /**
     * @return string
     */
    private function getEnabledCurrenciesConfigViewKey()
    {
        return $this->getConfigViewKey(Configuration::ENABLED_CURRENCIES);
    }

    /**
     * @param string $name
     * @return string
     */
    private function getConfigViewKey($name)
    {
        return implode(ConfigManager::SECTION_VIEW_SEPARATOR, [OroPricingProExtension::ALIAS, $name]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('full_currency_name', true);
    }
}
