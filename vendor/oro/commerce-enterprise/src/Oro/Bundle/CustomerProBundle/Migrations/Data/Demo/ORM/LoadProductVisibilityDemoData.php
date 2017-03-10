<?php

namespace Oro\Bundle\CustomerProBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MultiWebsiteBundle\Migrations\Data\Demo\ORM\LoadWebsiteDemoData;
use Oro\Bundle\VisibilityBundle\Migrations\Data\Demo\ORM\AbstractLoadProductVisibilityDemoData;

class LoadProductVisibilityDemoData extends AbstractLoadProductVisibilityDemoData
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return array_merge(parent::getDependencies(), [LoadWebsiteDemoData::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // @todo: fix BB-4506
//        $this->resetVisibilities($manager);
//
//        $locator = $this->container->get('file_locator');
//        $filePath = $locator->locate('@OroCustomerProBundle/Migrations/Data/Demo/ORM/data/products-visibility.csv');
//        $handler = fopen($filePath, 'r');
//        $headers = fgetcsv($handler, 1000, ',');
//
//        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
//            $row = array_combine($headers, array_values($data));
//
//            $product = $this->getProduct($manager, $row['product']);
//            $website = $this->getWebsite($manager, $row['website']);
//            $visibility = $row['visibility'];
//
//            $this->setProductVisibility($manager, $row, $website, $product, $visibility);
//        }
//
//        fclose($handler);
//        $manager->flush();
    }
}
