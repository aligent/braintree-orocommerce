<?php

namespace Oro\Bundle\UserProBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

use Oro\Bundle\UserProBundle\Entity\AuthenticationCode;

interface TwoFactorTransportInterface
{
    /**
     * @param  UserInterface       $user
     * @param  AuthenticationCode $code
     * @param  array              $clientData
     *
     * @return bool               Sent status
     */
    public function send(UserInterface $user, AuthenticationCode $code, array $clientData);
}
