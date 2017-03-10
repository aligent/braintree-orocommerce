<?php

namespace Oro\Bundle\OrganizationMenuBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Tests\Functional\DataFixtures\LoadLocalizationData;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdate;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\OrganizationProBundle\Tests\Functional\Fixture\LoadScopeOrganizationData;

use Oro\Component\Testing\Unit\EntityTrait;

class MenuUpdateData extends AbstractFixture implements DependentFixtureInterface
{
    use UserUtilityTrait;
    use EntityTrait;

    /** @var array */
    protected static $menuUpdates = [
        'organization_menu_update.1' => [
            'key' => 'organization_menu_update.1',
            'parent_key' => null,
            'default_title' => 'organization_menu_update.1.title',
            'titles' => [
                'en_US' => 'organization_menu_update.1.title.en_US',
                'en_CA' => 'organization_menu_update.1.title.en_CA',
            ],
            'default_description' => 'organization_menu_update.1.description',
            'descriptions' => [
                'en_US' => 'organization_menu_update.1.description.en_US',
                'en_CA' => 'organization_menu_update.1.description.en_CA',
            ],
            'uri' => '#menu_update.1',
            'menu' => 'application_menu',
            'scope' => LoadScopeOrganizationData::TEST_ORGANIZATION_SCOPE,
            'active' => true,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
        ],
        'organization_menu_update.1_1' => [
            'key' => 'organization_menu_update.1_1',
            'parent_key' => 'organization_menu_update.1',
            'default_title' => 'organization_menu_update.1_1.title',
            'titles' => [],
            'default_description' => 'organization_menu_update.1_1.description',
            'descriptions' => [],
            'uri' => '#organization_menu_update.1_1',
            'menu' => 'application_menu',
            'scope' => LoadScopeOrganizationData::TEST_ORGANIZATION_SCOPE,
            'active' => true,
            'priority' => 10,
            'divider' => false,
            'custom' => true,
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadLocalizationData::class,
            LoadScopeOrganizationData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {

        foreach (self::$menuUpdates as $menuUpdateReference => $data) {
            $titles = $data['titles'];
            unset($data['titles']);

            $descriptions = $data['descriptions'];
            unset($data['descriptions']);

            $scope = $this->getReference($data['scope']);
            unset($data['scope']);

            $entity = $this->getEntity(MenuUpdate::class, $data);
            $entity->setScope($scope);

            foreach ($titles as $localization => $title) {
                $fallbackValue = new LocalizedFallbackValue();
                $fallbackValue
                    ->setLocalization($this->getReference($localization))
                    ->setString($title);

                $entity->addTitle($fallbackValue);
            }

            foreach ($descriptions as $localization => $description) {
                $fallbackValue = new LocalizedFallbackValue();
                $fallbackValue
                    ->setLocalization($this->getReference($localization))
                    ->setText($description);

                $entity->addDescription($fallbackValue);
            }

            $this->setReference($menuUpdateReference, $entity);

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
