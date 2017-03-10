<?php

namespace Oro\Bundle\OrganizationBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SearchBundle\Async\Topics;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\OrganizationProBundle\Tests\Functional\Fixture\LoadUserPreferredOrganizationData;

/**
 * @dbIsolationPerTest
 */
class OrganizationTest extends WebTestCase
{
    use MessageQueueExtension;

    public function setUp()
    {
        parent::setUp();
        $this->initClient();
        $this->loadFixtures([LoadUserPreferredOrganizationData::class]);
    }

    public function testShouldScheduleReindexWhenUpdatingOrganizationIsGlobal()
    {
        /** @var Organization $organization */
        $organization = $this->getReference('mainOrganization');
        $organization->setIsGlobal(true);
        $this->getEntityManager()->persist($organization);
        $this->getEntityManager()->flush();

        self::getMessageCollector()->clear();

        $organization->setIsGlobal(false);

        $this->getEntityManager()->persist($organization);
        $this->getEntityManager()->flush();

        $traces = self::getMessageCollector()->getTopicSentMessages(Topics::REINDEX);

        self::assertCount(1, $traces);
    }

    public function testShouldNotScheduleReindexWhenUpdatingOrganizationDescriptionField()
    {
        /** @var Organization $organization */
        $organization = $this->getReference('mainOrganization');
        $organization->setDescription('Some Test Description');
        $this->getEntityManager()->persist($organization);
        $this->getEntityManager()->flush();

        self::assertEmptyMessages(Topics::REINDEX);
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
