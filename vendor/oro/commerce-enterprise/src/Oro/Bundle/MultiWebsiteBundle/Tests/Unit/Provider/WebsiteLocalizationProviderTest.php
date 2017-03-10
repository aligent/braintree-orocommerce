<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Provider;

use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\MultiWebsiteBundle\Provider\WebsiteLocalizationProvider;
use Oro\Bundle\WebsiteBundle\Tests\Unit\Provider\AbstractWebsiteLocalizationProviderTest;

class WebsiteLocalizationProviderTest extends AbstractWebsiteLocalizationProviderTest
{
    /** @var WebsiteLocalizationProvider */
    protected $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->provider = new WebsiteLocalizationProvider(
            $this->configManager,
            $this->localizationManager,
            $this->doctrineHelper
        );
    }

    public function testGetWebsiteLocalizations()
    {
        $websiteId = 42;
        $ids = [100, 200];

        $defaultLocalization = $this->getLocalization(200);
        $enabledLocalizations = [$this->getLocalization(100), $defaultLocalization];

        $localizations = [
            'default' => $defaultLocalization,
            'enabled' => $enabledLocalizations
        ];

        $this->configManager
            ->expects($this->at(0))
            ->method('setScopeId')
            ->with($websiteId);
        $this->configManager
            ->expects($this->at(1))
            ->method('get')
            ->with(sprintf('oro_locale.%s', Configuration::ENABLED_LOCALIZATIONS))
            ->willReturn($ids);
        $this->configManager
            ->expects($this->at(2))
            ->method('get')
            ->with(sprintf('oro_locale.%s', Configuration::DEFAULT_LOCALIZATION))
            ->willReturn(200);
        $this->configManager
            ->expects($this->at(3))
            ->method('setScopeId')
            ->with(null);

        $this->localizationManager->expects($this->once())
            ->method('getLocalizations')
            ->with($ids)
            ->willReturn($enabledLocalizations);

        $this->assertEquals($localizations, $this->provider->getWebsiteLocalizations($this->getWebsite($websiteId)));
    }

    public function testGetLocalizations()
    {
        $websiteId = 42;
        $ids = [100, 200];

        $localizations = [
            $this->getLocalization(100),
            $this->getLocalization(200),
        ];

        $this->configManager
            ->expects($this->at(0))
            ->method('getScopeId');
        $this->configManager
            ->expects($this->at(1))
            ->method('setScopeId')
            ->with($websiteId);
        $this->configManager
            ->expects($this->at(2))
            ->method('get')
            ->with(sprintf('oro_locale.%s', Configuration::ENABLED_LOCALIZATIONS))
            ->willReturn($ids);
        $this->configManager
            ->expects($this->at(3))
            ->method('setScopeId')
            ->with(null);

        $this->localizationManager->expects($this->once())
            ->method('getLocalizations')
            ->with($ids)
            ->willReturn($localizations);

        $this->assertEquals($localizations, $this->provider->getLocalizations($this->getWebsite($websiteId)));
    }
}
