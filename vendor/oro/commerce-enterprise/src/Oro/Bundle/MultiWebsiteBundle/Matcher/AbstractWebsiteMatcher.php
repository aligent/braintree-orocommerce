<?php

namespace Oro\Bundle\MultiWebsiteBundle\Matcher;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractWebsiteMatcher implements WebsiteMatcherInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var int[]|array
     */
    protected $websites;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @param ConfigManager $configManager
     * @param ManagerRegistry $registry
     * @param RequestStack $requestStack
     */
    public function __construct(
        ConfigManager $configManager,
        ManagerRegistry $registry,
        RequestStack $requestStack
    ) {
        $this->configManager = $configManager;
        $this->registry = $registry;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return array|int[]
     */
    protected function getWebsites()
    {
        if (null === $this->websites) {
            $em = $this->getEntityManager();
            /** @var WebsiteRepository $repo */
            $repo = $em->getRepository(Website::class);

            $this->websites = array_map(
                function ($id) use ($em) {
                    return $em->getReference(Website::class, $id);
                },
                $repo->getWebsiteIdentifiers()
            );
        }

        return $this->websites;
    }

    /**
     * @param int $id
     * @return Website
     */
    protected function getWebsiteReference($id)
    {
        return $this->getEntityManager()->getReference(Website::class, $id);
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->registry->getManagerForClass(Website::class);
    }

    /**
     * Get single config value with skipping of website scope to prevent infinite loop.
     *
     * @param string $name
     * @return mixed
     */
    protected function getConfigValue($name)
    {
        return $this->configManager->get($name, false, false, 0);
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getConfigValues($name)
    {
        return $this->configManager->getValues($name, $this->getWebsites());
    }
}
