<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class LoadConfig extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $currencyConfigs = Yaml::parse(file_get_contents(__DIR__ . '/configuration/currency.yml')) ? : [];
        foreach ($currencyConfigs as $scope => $configData) {
            $this->saveConfigDataToScope($scope, $configData);
        }
    }

    public function saveConfigDataToScope($scope, $configData)
    {
        $configService = $scope === 'organization' ? 'oro_config.organization' : 'oro_config.global';
        $configManager = $this->container->get($configService);
        $previousScopeId = null;
        if ($scope === 'organization') {
            $previousScopeId = $configManager->getScopeId();
            $configManager->setScopeId(WebTestCase::AUTH_ORGANIZATION);
        }
        foreach ($configData as $configKey => $configPropertyData) {
            $configManager->set(MultiCurrencyConfig::getConfigKeyByName($configKey), $configPropertyData);
        }
        $configManager->flush();
        if ($scope === 'organization') {
            $configManager->setScopeId($previousScopeId);
        }
    }
}
