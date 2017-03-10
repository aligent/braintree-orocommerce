<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Manager;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserProBundle\Entity\ClientData;
use Oro\Bundle\UserProBundle\Entity\Repository\ClientDataRepository;
use Oro\Bundle\UserProBundle\Manager\ClientDataManager;
use Oro\Bundle\UserProBundle\Provider\ClientDataProvider;

class ClientDataManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ClientDataManager */
    protected $clientDataManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Request */
    protected $request;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager */
    protected $em;

    /** @var User */
    protected $user;

    /** @var ClientData */
    protected $entity;

    /** @var ClientDataProvider */
    protected $clientDataProvider;

    protected function setUp()
    {
        $this->user = new User();
        $ip = '255.255.255.255';
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)';

        $this->entity = new ClientData();
        $this->entity->setUser($this->user);
        $this->entity->setIpAddress($ip);
        $this->entity->setUserAgent($userAgent);

        $this->em = $this->getMockForClass(ObjectManager::class);
        $doctrine = $this->getMockForClass(RegistryInterface::class);
        $doctrine->expects(self::atLeastOnce())
            ->method('getManagerForClass')
            ->willReturn($this->em);

        $this->clientDataProvider = $this->getMockForClass(ClientDataProvider::class);

        $this->clientDataManager = new ClientDataManager($doctrine, $this->clientDataProvider);
    }

    /**
     *  Test the creation of a new ClientData
     */
    public function testAddClientData()
    {
        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($this->entity));
        $this->em->expects($this->once())
            ->method('flush');

        $this->clientDataProvider
            ->expects($this->once())
            ->method('getIpAddress')
            ->willReturn($this->entity->getIpAddress());
        $this->clientDataProvider
            ->expects($this->once())
            ->method('getUserAgent')
            ->willReturn($this->entity->getUserAgent());

        $this->clientDataManager->addClientData($this->user);
    }

    /**
     * Test if entity with given properties already exists
     */
    public function testIsClientRecognized()
    {
        $repo = $this->getMockForClass(ClientDataRepository::class);
        $repo->expects($this->once())
            ->method('hasClient')
            ->willReturn(true);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repo);
        $this->assertTrue($this->clientDataManager->isClientRecognized($this->user));
    }

    /**
     * Get Mock for class with disabled constructor
     *
     * @param $class
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|$class
     */
    protected function getMockForClass($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
