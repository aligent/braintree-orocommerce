<?php

namespace Oro\Bundle\MultiWebsiteBundle\Provider;

use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\AbstractWebsiteLocalizationProvider;

class WebsiteLocalizationProvider extends AbstractWebsiteLocalizationProvider
{
    /**
     * @param Website $website
     * @return array|Localization[]
     */
    public function getWebsiteLocalizations(Website $website)
    {
        $this->configManager->setScopeId($website->getId());

        $localizations = $this->localizationManager->getLocalizations($this->getEnabledLocalizationIds());
        $defaultLocalization = null;

        $defaultLocalizationId = $this->getDefaultLocalizationId();
        foreach ($localizations as $localization) {
            if ($localization->getId() == $defaultLocalizationId) {
                $defaultLocalization = $localization;
            }
        }

        $this->configManager->setScopeId(null);

        return [
            'default' => $defaultLocalization,
            'enabled' => $localizations
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getLocalizations(Website $website)
    {
        $prevScopeId = $this->configManager->getScopeId();

        $this->configManager->setScopeId($website->getId());
        $localizations = $this->localizationManager->getLocalizations($this->getEnabledLocalizationIds());

        $this->configManager->setScopeId($prevScopeId);

        return $localizations;
    }

    /**
     * @return int
     */
    protected function getDefaultLocalizationId()
    {
        return $this->configManager->get(Configuration::getConfigKeyByName(Configuration::DEFAULT_LOCALIZATION));
    }
}
