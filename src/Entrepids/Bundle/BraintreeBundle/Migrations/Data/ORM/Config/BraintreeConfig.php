<?php

namespace Entrepids\Bundle\BraintreeBundle\Migrations\Data\ORM\Config;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Entrepids\Bundle\BraintreeBundle\DependencyInjection\BraintreeExtension;

class BraintreeConfig
{
    const BRAINTREE_LABEL_KEY = 'braintree_label';
    const BRAINTREE_SHORT_LABEL_KEY = 'braintree_short_label';

    const BRAINTREE_LABEL = 'Braintree';

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPaymentExtensionAlias()
    {
        return BraintreeExtension::ALIAS;
    }

    /**
     * @return LocalizedFallbackValue
     */
    public function getLabel()
    {
        return $this->getLocalizedFallbackValueFromConfig(
            self::BRAINTREE_LABEL_KEY,
            self::BRAINTREE_LABEL
        );
    }

    /**
     * @return LocalizedFallbackValue
     */
    public function getShortLabel()
    {
        return $this->getLocalizedFallbackValueFromConfig(
            self::BRAINTREE_SHORT_LABEL_KEY,
            self::BRAINTREE_LABEL
        );
    }

     /**
     * @return bool
     */
    public function isAllRequiredFieldsSet()
    {
        $fields = [
            $this->getLabel(),
            $this->getShortLabel(),

        ];

        foreach ($fields as $field) {
            if (empty($field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    private function getConfigValue($key)
    {
        return $this->configManager->get($this->getFullConfigKey($key));
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function getFullConfigKey($key)
    {
        return BraintreeExtension::ALIAS . ConfigManager::SECTION_MODEL_SEPARATOR . $key;
    }

    /**
     * @param string $key
     * @param string $default
     *
     * @return LocalizedFallbackValue
     */
    private function getLocalizedFallbackValueFromConfig($key, $default)
    {
        $creditCardLabel = $this->getConfigValue($key);
        
        return (new LocalizedFallbackValue())->setString($creditCardLabel ?: $default);
    }
}
