<?php

namespace Oro\Bundle\EwsBundle\Tests\Unit\Entity\Repository;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\EntityManagerMock;
use Oro\Bundle\EwsBundle\Entity\EwsEmailOrigin;
use Oro\Bundle\EwsBundle\Entity\Repository\EwsEmailFolderRepository;

class EwsEmailFolderRepositoryTest extends OrmTestCase
{
    /** @var EntityManagerMock */
    protected $em;

    protected function setUp()
    {
        $reader         = new AnnotationReader();
        $metadataDriver = new AnnotationDriver(
            $reader,
            [
                'Oro\Bundle\EwsBundle\Entity',
                'Oro\Bundle\EmailBundle\Entity',
            ]
        );

        $this->em = $this->getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        $this->em->getConfiguration()->setEntityNamespaces(
            [
                'OroEwsBundle' => 'Oro\Bundle\EwsBundle\Entity'
            ]
        );
    }

    public function testGetFoldersByOriginQueryBuilder()
    {
        $origin = new EwsEmailOrigin();

        /** @var EwsEmailFolderRepository $repo */
        $repo = $this->em->getRepository('OroEwsBundle:EwsEmailFolder');

        $qb    = $repo->getFoldersByOriginQueryBuilder($origin);
        $query = $qb->getQuery();

        $this->assertEquals(
            'SELECT ews_folder'
            . ' FROM Oro\Bundle\EwsBundle\Entity\EwsEmailFolder ews_folder'
            . ' INNER JOIN ews_folder.folder folder'
            . ' WHERE folder.origin = :origin AND folder.outdatedAt IS NULL',
            $query->getDQL()
        );

        $this->assertSame($origin, $query->getParameter('origin')->getValue());
    }

    public function testGetFoldersByOriginQueryBuilderWithOutdated()
    {
        $origin = new EwsEmailOrigin();

        /** @var EwsEmailFolderRepository $repo */
        $repo = $this->em->getRepository('OroEwsBundle:EwsEmailFolder');

        $qb    = $repo->getFoldersByOriginQueryBuilder($origin, true);
        $query = $qb->getQuery();

        $this->assertEquals(
            'SELECT ews_folder'
            . ' FROM Oro\Bundle\EwsBundle\Entity\EwsEmailFolder ews_folder'
            . ' INNER JOIN ews_folder.folder folder'
            . ' WHERE folder.origin = :origin',
            $query->getDQL()
        );

        $this->assertSame($origin, $query->getParameter('origin')->getValue());
    }
}
