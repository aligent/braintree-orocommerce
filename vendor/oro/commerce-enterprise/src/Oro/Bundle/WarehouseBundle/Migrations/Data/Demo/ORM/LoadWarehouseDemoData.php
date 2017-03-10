<?php

namespace Oro\Bundle\WarehouseBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\MigrationBundle\Fixture\AbstractEntityReferenceFixture;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\InventoryBundle\Migrations\Data\Demo\ORM\LoadInventoryLevelDemoData;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;

class LoadWarehouseDemoData extends AbstractEntityReferenceFixture implements DependentFixtureInterface
{
    use UserUtilityTrait;

    const MAIN_WAREHOUSE = 'warehouse.main';
    const ADDITIONAL_WAREHOUSE = 'warehouse.additional.1';
    const ADDITIONAL_WAREHOUSE_2 = 'warehouse.additional.2';

    /**
     * @var array
     */
    protected $warehouses = [
        self::MAIN_WAREHOUSE => [
            'name' => 'Main Warehouse',
            'generateLevels' => true,
        ],
        self::ADDITIONAL_WAREHOUSE => [
            'name' => 'Additional Warehouse',
            'generateLevels' => true,
        ],
        self::ADDITIONAL_WAREHOUSE_2 => [
            'name' => 'Additional Warehouse 2',
            'generateLevels' => true,
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadInventoryLevelDemoData::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        $user = $this->getFirstUser($manager);
        $businessUnit = $user->getOwner();
        $organization = $user->getOrganization();
        $precisions   = $this->getObjectReferences($manager, 'OroProductBundle:ProductUnitPrecision');
        $inventoryLevels = $this->getObjectReferences($manager, 'OroInventoryBundle:InventoryLevel');
        $processedInventories = false;

        foreach ($this->warehouses as $reference => $row) {
            $warehouse = new Warehouse();
            $warehouse
                ->setName($row['name'])
                ->setOwner($businessUnit)
                ->setOrganization($organization);
            $manager->persist($warehouse);

            if (!empty($row['generateLevels'])) {
                if (!$processedInventories) {
                    $processedInventories = true;
                    foreach ($inventoryLevels as $inventoryLevel) {
                        $inventoryLevel->setWarehouse($warehouse);

                        $manager->persist($inventoryLevel);
                    }
                } else {
                    foreach ($precisions as $precision) {
                        $level = new InventoryLevel();
                        $level
                            ->setWarehouse($warehouse)
                            ->setProductUnitPrecision($precision)
                            ->setQuantity(mt_rand(1, 100));
                        $manager->persist($level);
                    }
                }
            }

            $this->addReference($reference, $warehouse);
        }

        $manager->flush();
    }
}
