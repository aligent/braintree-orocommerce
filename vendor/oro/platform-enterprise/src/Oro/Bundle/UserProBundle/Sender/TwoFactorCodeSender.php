<?php

namespace Oro\Bundle\UserProBundle\Sender;

use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserProBundle\Entity\AuthenticationCode;
use Oro\Bundle\UserProBundle\Provider\ClientDataProvider;
use Oro\Bundle\UserProBundle\Security\TwoFactorTransportInterface;

class TwoFactorCodeSender
{
    /** @var ClientDataProvider */
    protected $clientDataProvider;

    /** @var TwoFactorTransportInterface */
    protected $transport;

    /**
     * @param ClientDataProvider $clientDataProvider
     * @param TwoFactorTransportInterface $transport
     */
    public function __construct(
        ClientDataProvider $clientDataProvider,
        TwoFactorTransportInterface $transport
    ) {
        $this->clientDataProvider = $clientDataProvider;
        $this->transport = $transport;
    }

    /**
     * Send authentication $code to $user
     *
     * @param AbstractUser $user
     * @param AuthenticationCode $code
     *
     * @return bool Send status
     */
    public function send(AbstractUser $user, AuthenticationCode $code)
    {
        return $this->transport->send($user, $code, $this->getClientData());
    }

    /**
     * @return array
     */
    protected function getClientData()
    {
        return [
            'loginAt' => new \DateTime('now', new \DateTimeZone('UTC')),
            'ipAddress' => $this->clientDataProvider->getIpAddress(),
            'browser' => $this->clientDataProvider->getBrowser(),
            'platform' => $this->clientDataProvider->getPlatform(),
        ];
    }
}
