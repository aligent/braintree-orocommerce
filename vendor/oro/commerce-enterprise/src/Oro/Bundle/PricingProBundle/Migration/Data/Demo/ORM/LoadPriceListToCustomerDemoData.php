<?php

namespace Oro\Bundle\PricingProBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\PricingBundle\Entity\PriceListToCustomer;
use Oro\Bundle\PricingBundle\Migrations\Data\Demo\ORM\LoadBasePriceListRelationDemoData;
use Oro\Bundle\MultiWebsiteBundle\Migrations\Data\Demo\ORM\LoadWebsiteDemoData;

class LoadPriceListToCustomerDemoData extends LoadBasePriceListRelationDemoData
{
    /**
     * @var Customer[]
     */
    protected $customers;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $locator = $this->container->get('file_locator');
        $filePath = $locator
            ->locate('@OroPricingProBundle/Migrations/Data/Demo/ORM/data/price_lists_to_customer.csv');

        if (is_array($filePath)) {
            $filePath = current($filePath);
        }

        $handler = fopen($filePath, 'r');
        $headers = fgetcsv($handler, 1000, ',');
        /** @var EntityManager $manager */
        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
            $row = array_combine($headers, array_values($data));
            $customer = $this->getCustomerByName($manager, $row['customer']);
            $priceList = $this->getPriceListByName($manager, $row['priceList']);
            $website = $this->getWebsiteByName($manager, $row['website']);

            $priceListToCustomer = new PriceListToCustomer();
            $priceListToCustomer->setCustomer($customer)
                ->setPriceList($priceList)
                ->setWebsite($website)
                ->setPriority($row['priority'])
                ->setMergeAllowed((boolean)$row['mergeAllowed']);

            $manager->persist($priceListToCustomer);
        }

        fclose($handler);

        $manager->flush();
    }

    /**
     * @param EntityManager $manager
     * @param string $name
     * @return Customer
     */
    protected function getCustomerByName(EntityManager $manager, $name)
    {
        foreach ($this->getCustomers($manager) as $customer) {
            if ($customer->getName() === $name) {
                return $customer;
            }
        }

        throw new \LogicException(sprintf('There is no customer with name "%s" .', $name));
    }

    /**
     * @param EntityManager $manager
     * @return array|Customer[]
     */
    protected function getCustomers(EntityManager $manager)
    {
        if (!$this->customers) {
            $this->customers = $manager->getRepository('OroCustomerBundle:Customer')->findAll();
        }

        return $this->customers;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return array_merge(parent::getDependencies(), [LoadWebsiteDemoData::class]);
    }
}
