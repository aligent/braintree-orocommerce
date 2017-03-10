<?php

namespace Oro\Bundle\CustomerProBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MultiWebsiteBundle\Migrations\Data\Demo\ORM\LoadWebsiteDemoData;
use Oro\Bundle\WebsiteBundle\Migrations\Data\ORM\LoadWebsiteData;

class LoadWebsiteDefaultRoles extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadWebsiteDemoData::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $websites = $manager->getRepository('OroWebsiteBundle:Website')->findAll();
        $defaultWebsite = $manager->getRepository('OroWebsiteBundle:Website')
            ->findOneBy(['name' => LoadWebsiteData::DEFAULT_WEBSITE_NAME]);

        // Remove default site. Default role for it already exist
        unset($websites[array_search($defaultWebsite, $websites)]);

        $allRoles = $manager->getRepository('OroCustomerBundle:CustomerUserRole')->findAll();
        foreach ($websites as $website) {
            $role = $allRoles[mt_rand(0, count($allRoles) - 1)];
            $role->addWebsite($website);

            $manager->persist($role);
        }

        $manager->flush();
    }
}
