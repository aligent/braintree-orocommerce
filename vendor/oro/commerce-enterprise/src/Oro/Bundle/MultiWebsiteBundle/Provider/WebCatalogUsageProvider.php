<?php

namespace Oro\Bundle\MultiWebsiteBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogUsageProvider as CommunityWebCatalogUsageProvider;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class WebCatalogUsageProvider extends CommunityWebCatalogUsageProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var Website[]
     */
    protected $websites;

    /**
     * @param ConfigManager $configManager
     * @param ManagerRegistry $registry
     */
    public function __construct(
        ConfigManager $configManager,
        ManagerRegistry $registry
    ) {
        parent::__construct($configManager);
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function isInUse(WebCatalog $webCatalog)
    {
        if (parent::isInUse($webCatalog)) {
            return true;
        }

        $webCatalogsAssignedToWebsites = $this->configManager->getValues(
            CommunityWebCatalogUsageProvider::SETTINGS_KEY,
            $this->getWebsites()
        );

        foreach ($webCatalogsAssignedToWebsites as $webCatalogsAssignedToWebsite) {
            if ((int)$webCatalogsAssignedToWebsite === $webCatalog->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Website[]
     */
    protected function getWebsites()
    {
        if (null === $this->websites) {
            /** @var EntityManagerInterface $em */
            $em = $this->registry->getManagerForClass(Website::class);

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
}
