<?php

namespace Oro\Bundle\UserProBundle\Security;

use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver as BaseAuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Make TwoFactorToken behave the same as Anonymous token
 *
 * Extends the original AuthenticationTrustResolver because the
 * ACL security bundle depends on that class instead of using the interface.
 *
 */
class AuthenticationTrustResolver extends BaseAuthenticationTrustResolver
{
    /**
     * @var AuthenticationTrustResolverInterface
     */
    private $decoratedResolver;

    /**
     * @param AuthenticationTrustResolverInterface $decoratedResolver
     */
    public function __construct(AuthenticationTrustResolverInterface $decoratedResolver)
    {
        $this->decoratedResolver = $decoratedResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isAnonymous(TokenInterface $token = null)
    {
        return $this->isTwoFactorToken($token) || $this->decoratedResolver->isAnonymous($token);
    }

    /**
     * {@inheritdoc}
     */
    public function isRememberMe(TokenInterface $token = null)
    {
        return $this->decoratedResolver->isRememberMe($token);
    }

    /**
     * {@inheritdoc}
     */
    public function isFullFledged(TokenInterface $token = null)
    {
        return !$this->isTwoFactorToken($token) && $this->decoratedResolver->isFullFledged($token);
    }

    /**
     * @param TokenInterface|null $token
     *
     * @return bool
     */
    private function isTwoFactorToken(TokenInterface $token = null)
    {
        return $token instanceof TwoFactorToken;
    }
}
