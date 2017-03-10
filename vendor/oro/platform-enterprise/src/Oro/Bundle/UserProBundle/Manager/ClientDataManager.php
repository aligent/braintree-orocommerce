<?php

namespace Oro\Bundle\UserProBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Oro\Bundle\UserProBundle\Entity\ClientData;
use Oro\Bundle\UserProBundle\Provider\ClientDataProvider;

class ClientDataManager
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var ClientDataProvider */
    protected $clientDataProvider;

    /**
     * @param RegistryInterface $doctrine
     * @param ClientDataProvider $clientDataProvider
     */
    public function __construct(RegistryInterface $doctrine, ClientDataProvider $clientDataProvider)
    {
        $this->doctrine = $doctrine;
        $this->clientDataProvider = $clientDataProvider;
    }

    /**
     * @param UserInterface $user
     */
    public function addClientData(UserInterface $user)
    {
        $clientData = new ClientData();
        $clientData->setUser($user);
        $clientData->setIpAddress($this->clientDataProvider->getIpAddress());
        $clientData->setUserAgent($this->clientDataProvider->getUserAgent());
        // save
        $em = $this->getClientDataEntityManager();
        $em->persist($clientData);
        $em->flush();
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isClientRecognized(UserInterface $user)
    {
        return $this->getClientDataRepository()->hasClient(
            $user,
            $this->clientDataProvider->getIpAddress(),
            $this->clientDataProvider->getUserAgent()
        );
    }

    /**
     * @return ObjectManager
     */
    protected function getClientDataEntityManager()
    {
        return $this->doctrine->getManagerForClass(ClientData::class);
    }

    /**
     * @return ObjectRepository
     */
    protected function getClientDataRepository()
    {
        return $this->getClientDataEntityManager()->getRepository(ClientData::class);
    }
}
