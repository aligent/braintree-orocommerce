<?php

namespace Oro\Bundle\UserProBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\UserProBundle\Security\TwoFactorToken;

class TwoFactorAuthenticationEvent extends Event
{
    /** @var TwoFactorToken */
    protected $token;

    /**
     * @param TwoFactorToken $token
     */
    public function __construct(TwoFactorToken $token)
    {
        $this->token = $token;
    }

    /**
     * @return TwoFactorToken
     */
    public function getToken()
    {
        return $this->token;
    }
}
