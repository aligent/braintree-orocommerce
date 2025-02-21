<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Form\Type;

use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PayPalSettingsType extends AbstractType
{
    protected \Symfony\Contracts\Translation\TranslatorInterface $translator;

    protected array $locales;

    /**
     * PayPalSettingsType constructor.
     */
    public function __construct(\Symfony\Contracts\Translation\TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'aligent.braintree.settings.paypal.enabled'
                ]
            )
            ->add(
                'flow',
                ChoiceType::class,
                [
                    'label'    =>  'aligent.braintree.settings.paypal.flow.label',
                    'required' => false,
                    'choices'  => [
                        $this->translator->trans('aligent.braintree.settings.paypal.flow.checkout.label')
                            => BraintreeConfigInterface::PAYPAL_FLOW_CHECKOUT,
                        $this->translator->trans('aligent.braintree.settings.paypal.flow.vault.label')
                            => BraintreeConfigInterface::PAYPAL_FLOW_VAULT
                    ],
                    'empty_data' => BraintreeConfigInterface::PAYPAL_FLOW_VAULT
                ]
            )
            ->add(
                'displayName',
                TextType::class,
                [
                    'label'    => 'aligent.braintree.settings.paypal.display_name.label',
                    'tooltip'  => $this->translator->trans('aligent.braintree.settings.paypal.display_name.tooltip'),
                    'required' => false
                ]
            )
            ->add(
                'locale',
                ChoiceType::class,
                [
                    'label'    => 'aligent.braintree.settings.paypal.locale.label',
                    'choices'  => $this->getLocaleChoices(),
                    'required' => false
                ]
            )
            ->add(
                'landingPageType',
                ChoiceType::class,
                [
                    'label'    => 'aligent.braintree.settings.paypal.landing_page.label',
                    'tooltip'  =>  $this->translator->trans('aligent.braintree.settings.paypal.landing_page.tooltip'),
                    'required' => false,
                    'choices'  => [
                        $this->translator->trans('aligent.braintree.settings.paypal.landing_page.login')
                            => BraintreeConfigInterface::PAYPAL_LOGIN_PAGE,
                        $this->translator->trans('aligent.braintree.settings.paypal.landing_page.billing')
                            => BraintreeConfigInterface::PAYPAL_BILLING_PAGE
                    ],
                    'empty_data' => BraintreeConfigInterface::PAYPAL_LOGIN_PAGE
                ]
            );
    }

    /**
     * Fetch array of locale choices paypal supports
     */
    protected function getLocaleChoices()
    {
        if (!$this->locales) {
            $this->locales =  [
                $this->translator->trans('aligent.braintree.settings.paypal.locale.da_DK') => 'da_DK',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.de_DE') => 'de_DE',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.en_AU') => 'en_AU',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.en_GB') => 'en_GB',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.en_US') => 'en_US',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.es_ES') => 'es_ES',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.fr_CA') => 'fr_CA',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.fr_FR') => 'fr_FR',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.id_ID') => 'id_ID',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.it_IT') => 'it_IT',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.ja_JP') => 'ja_JP',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.ko_KR') => 'ko_KR',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.nl_NL') => 'nl_NL',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.no_NO') => 'no_NO',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.pl_PL') => 'pl_PL',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.pt_BR') => 'pt_BR',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.pt_PT') => 'pt_PT',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.ru_RU') => 'ru_RU',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.sv_SE') => 'sv_SE',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.th_TH') => 'th_TH',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.zh_CN') => 'zh_CN',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.zh_HK') => 'zh_HK',
                $this->translator->trans('aligent.braintree.settings.paypal.locale.zh_TW') => 'zh_TW',
            ];
        }

        return $this->locales;
    }
}
