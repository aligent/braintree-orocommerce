<?php

namespace Oro\Bundle\UserProBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface DecoratedTokenFactoryInterface
{
    /**
     * Create/decorate token based on existing token
     *
     * @param  TokenInterface $decoratedToken
     * @return TokenInterface
     */
    public function create(TokenInterface $decoratedToken);
}
