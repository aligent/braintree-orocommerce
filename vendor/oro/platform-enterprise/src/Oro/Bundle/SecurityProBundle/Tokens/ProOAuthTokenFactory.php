<?php

namespace Oro\Bundle\SecurityProBundle\Tokens;

use Oro\Bundle\SSOBundle\Security\OAuthTokenFactoryInterface;

class ProOAuthTokenFactory implements OAuthTokenFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create($accessToken, array $roles = [])
    {
        return new ProOAuthToken($accessToken, $roles);
    }
}
