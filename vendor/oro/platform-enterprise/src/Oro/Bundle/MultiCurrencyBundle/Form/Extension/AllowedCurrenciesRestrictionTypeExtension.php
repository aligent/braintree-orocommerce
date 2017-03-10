<?php

namespace Oro\Bundle\MultiCurrencyBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class AllowedCurrenciesRestrictionTypeExtension extends AbstractTypeExtension
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * AllowedCurrenciesRestrictionTypeExtension constructor.
     *
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'restrict' => false
        ]);

        $resolver->setAllowedTypes([
            'restrict' => 'bool'
        ]);

        $resolver->setNormalizers([
            'choices' => function (Options $options, $value) {
                if (!$this->isApplicable($options)) {
                    return $value;
                } else {
                    $allowedCurrencies = $this->configManager->get(
                        MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_ALLOWED_CURRENCIES)
                    );
                    return array_intersect(
                        $value,
                        $allowedCurrencies
                    );
                }
            }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'Oro\Bundle\CurrencyBundle\Form\Type\CurrencyType';
    }

    /**
     * Returns true if extension is applicable for specific form type
     *
     * @param Options $options
     * @return bool
     */
    protected function isApplicable(Options $options)
    {
        return (isset($options['restrict'])
             && $options['restrict']
             && $this->configManager->get(
                 MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_ALLOWED_CURRENCIES)
             )
        );
    }
}
