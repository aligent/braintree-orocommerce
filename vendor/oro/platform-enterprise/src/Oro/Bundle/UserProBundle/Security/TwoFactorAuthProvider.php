<?php

namespace Oro\Bundle\UserProBundle\Security;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Oro\Bundle\UserProBundle\Manager\ClientDataManager;
use Oro\Bundle\UserProBundle\Manager\TwoFactorCodeManager;

class TwoFactorAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var TwoFactorCodeManager
     */
    protected $codeManager;

    /**
     * @var ClientDataManager
     */
    protected $clientDataManager;

    /**
     * @var string
     */
    protected $providerKey;

    /**
     * @param TwoFactorCodeManager $codeManager
     * @param ClientDataManager $clientDataManager
     * @param UserProviderInterface $userProvider
     * @param string $providerKey
     */
    public function __construct(
        TwoFactorCodeManager $codeManager,
        ClientDataManager $clientDataManager,
        UserProviderInterface $userProvider,
        $providerKey
    ) {
        $this->codeManager = $codeManager;
        $this->clientDataManager = $clientDataManager;
        $this->userProvider = $userProvider;
        $this->providerKey = $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if ($token->isAuthenticated()) {
            return $token;
        }

        $user = $this->userProvider->refreshUser($token->getUser());
        $userCode = $this->codeManager->find($user);

        if (!$userCode || !$token->getCredentials()) {
            throw new BadCredentialsException();
        }

        if ($token->getCredentials() !== $userCode) {
            throw new BadCredentialsException();
        }

        $token->setUser($user);
        $token->setAuthenticated(true);
        $this->codeManager->invalidate($user);
        $this->clientDataManager->addClientData($token->getUser());

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof TwoFactorToken;
    }
}
