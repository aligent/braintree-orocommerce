<?php

namespace Oro\Bundle\MultiWebsiteBundle\Manager;

use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherRegistry;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager as CommunityWebsiteManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WebsiteManager extends CommunityWebsiteManager implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param Website $website
     */
    public function setDefaultWebsite(Website $website)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->createQueryBuilder()
            ->update('OroWebsiteBundle:Website', 'website')
            ->set('website.default', ':default_false')
            ->where('website.default = :default_true')
            ->setParameter('default_true', true)
            ->setParameter('default_false', false)
            ->getQuery()
            ->execute();

        $website->setDefault(true);
        $entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getResolvedWebsite()
    {
        if (null === $this->currentWebsite) {
            foreach ($this->getMatcherRegistry()->getEnabledMatchers() as $matcher) {
                if ($website = $matcher->match()) {
                    $this->currentWebsite = $website;
                    break;
                }
            }

            if (!$this->currentWebsite) {
                $this->currentWebsite = parent::getResolvedWebsite();
            }
        }

        return $this->currentWebsite;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return WebsiteMatcherRegistry
     */
    protected function getMatcherRegistry()
    {
        return $this->container->get('oro_multiwebsite.matcher.website_matcher_registry');
    }
}
