<?php

namespace Oro\Bundle\PricingProBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\PricingBundle\Entity\PriceListToCustomerGroup;
use Oro\Bundle\PricingBundle\Migrations\Data\Demo\ORM\LoadBasePriceListRelationDemoData;
use Oro\Bundle\MultiWebsiteBundle\Migrations\Data\Demo\ORM\LoadWebsiteDemoData;

class LoadPriceListToCustomerGroupDemoData extends LoadBasePriceListRelationDemoData
{
    /**
     * @var CustomerGroup[]
     */
    protected $customerGroups;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $locator = $this->container->get('file_locator');
        $filePath = $locator
            ->locate('@OroPricingProBundle/Migrations/Data/Demo/ORM/data/price_lists_to_customer_group.csv');

        if (is_array($filePath)) {
            $filePath = current($filePath);
        }

        $handler = fopen($filePath, 'r');
        $headers = fgetcsv($handler, 1000, ',');

        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
            $row = array_combine($headers, array_values($data));
            /** @var EntityManager $manager */
            $customer = $this->getCustomerGroupByName($manager, $row['customerGroup']);
            $priceList = $this->getPriceListByName($manager, $row['priceList']);
            $website = $this->getWebsiteByName($manager, $row['website']);

            $priceListToCustomerGroup = new PriceListToCustomerGroup();
            $priceListToCustomerGroup->setCustomerGroup($customer)
                ->setPriceList($priceList)
                ->setWebsite($website)
                ->setPriority($row['priority'])
                ->setMergeAllowed((boolean)$row['mergeAllowed']);

            $manager->persist($priceListToCustomerGroup);
        }

        fclose($handler);

        $manager->flush();
    }

    /**
     * @param EntityManager $manager
     * @param string $name
     * @return CustomerGroup
     */
    protected function getCustomerGroupByName(EntityManager $manager, $name)
    {

        foreach ($this->getCustomerGroups($manager) as $customerGroup) {
            if ($customerGroup->getName() === $name) {
                return $customerGroup;
            }
        }

        throw new \LogicException(sprintf('There is no customer group with name "%s" .', $name));
    }

    /**
     * @param EntityManager $manager
     * @return array|CustomerGroup[]
     */
    protected function getCustomerGroups(EntityManager $manager)
    {
        if ($this->customerGroups) {
            $this->customerGroups = $manager->getRepository('OroCustomerBundle:CustomerGroup')->findAll();
        }

        return $this->customerGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return array_merge(parent::getDependencies(), [LoadWebsiteDemoData::class]);
    }
}
