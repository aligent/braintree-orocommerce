<?php

namespace Oro\Bundle\SecurityCRMProBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class SetShareGridConfig extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected static $entitiesSecurityConfig = [
        ['Oro\Bundle\AccountBundle\Entity\Account', 'share_scopes', ['user']],
        ['Oro\Bundle\CallBundle\Entity\Call', 'share_scopes', ['user']],
        ['Oro\Bundle\CaseBundle\Entity\CaseEntity', 'share_scopes', ['user']],
        ['Oro\Bundle\ContactBundle\Entity\Contact', 'share_scopes', ['user']],
        ['Oro\Bundle\ContactUsBundle\Entity\ContactRequest', 'share_scopes', ['user']],
        ['Oro\Bundle\SalesBundle\Entity\Lead', 'share_scopes', ['user']],
        ['Oro\Bundle\SalesBundle\Entity\Opportunity', 'share_scopes', ['user']],
        ['Oro\Bundle\SalesBundle\Entity\SalesFunnel', 'share_scopes', ['user']],
        ['Oro\Bundle\TaskBundle\Entity\Task', 'share_scopes', ['user']],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configManager = $this->container->get('oro_entity_config.config_manager');

        foreach (self::$entitiesSecurityConfig as $securityConfig) {
            $this->setEntityConfig($configManager, $securityConfig[0], $securityConfig[1], $securityConfig[2]);
        }

        $configManager->flush();
    }

    /**
     * @param ConfigManager $configManager
     * @param string $entityClass
     * @param string $code
     * @param mixed $value
     */
    protected function setEntityConfig(ConfigManager $configManager, $entityClass, $code, $value)
    {
        if (!$configManager->hasConfig($entityClass)) {
            return;
        }

        $entityConfig = $configManager->getProvider('security')->getConfig($entityClass);
        $entityConfig->set($code, $value);
        $configManager->persist($entityConfig);
    }
}
