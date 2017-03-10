<?php

namespace Oro\Bundle\PricingProBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\MultiWebsiteBundle\Migrations\Data\Demo\ORM\LoadWebsiteDemoData;
use Oro\Bundle\PricingBundle\Entity\PriceListToWebsite;
use Oro\Bundle\PricingBundle\Migrations\Data\Demo\ORM\LoadBasePriceListRelationDemoData;
use Oro\Bundle\PricingBundle\Migrations\Data\Demo\ORM\LoadPriceListDemoData;

class LoadPriceListToWebsiteDemoData extends LoadBasePriceListRelationDemoData
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $locator = $this->container->get('file_locator');
        $filePath = $locator
            ->locate('@OroPricingProBundle/Migrations/Data/Demo/ORM/data/price_lists_to_website.csv');

        if (is_array($filePath)) {
            $filePath = current($filePath);
        }

        $handler = fopen($filePath, 'r');
        $headers = fgetcsv($handler, 1000, ',');

        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
            $row = array_combine($headers, array_values($data));
            /** @var EntityManager $manager */
            $priceList = $this->getPriceListByName($manager, $row['priceList']);
            $website = $this->getWebsiteByName($manager, $row['website']);

            $priceListToCustomer = new PriceListToWebsite();
            $priceListToCustomer->setWebsite($website)
                ->setPriceList($priceList)
                ->setPriority($row['priority'])
                ->setMergeAllowed((boolean)$row['mergeAllowed']);
            $manager->persist($priceListToCustomer);
        }

        fclose($handler);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadWebsiteDemoData::class, LoadPriceListDemoData::class];
    }
}
