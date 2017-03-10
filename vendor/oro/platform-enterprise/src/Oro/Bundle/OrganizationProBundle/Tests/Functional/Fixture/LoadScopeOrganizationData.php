<?php

namespace Oro\Bundle\OrganizationProBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ScopeBundle\Entity\Scope;

class LoadScopeOrganizationData extends AbstractFixture implements DependentFixtureInterface
{
    const TEST_ORGANIZATION_SCOPE = 'test_organization_scope';

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganizationUsersData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $scope = new Scope();
        $scope->setOrganization($this->getReference(LoadOrganizationUsersData::TEST_ORGANIZATION));
        $manager->persist($scope);
        $manager->flush();
        $this->setReference(self::TEST_ORGANIZATION_SCOPE, $scope);
    }
}
