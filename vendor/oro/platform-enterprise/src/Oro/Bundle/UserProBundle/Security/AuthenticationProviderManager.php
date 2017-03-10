<?php

namespace Oro\Bundle\UserProBundle\Security;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\SecurityProBundle\Tokens\ProUsernamePasswordOrganizationToken;
use Oro\Bundle\UserProBundle\Event\TwoFactorAuthenticationEvent;
use Oro\Bundle\UserProBundle\Event\TwoFactorAuthenticationEvents;
use Oro\Bundle\UserProBundle\Manager\ClientDataManager;
use Oro\Bundle\UserProBundle\Provider\TFAConfigProvider;

class AuthenticationProviderManager implements AuthenticationManagerInterface
{
    /** @var AuthenticationManagerInterface $decoratedManager */
    protected $decoratedManager;

    /** @var DecoratedTokenFactoryInterface $tokenFactory */
    protected $tokenFactory;

    /** @var TFAConfigProvider */
    protected $configProvider;

    /** @var ClientDataManager */
    protected $clientDataManager;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param AuthenticationManagerInterface $decoratedManager
     * @param DecoratedTokenFactoryInterface $tokenFactory
     * @param TFAConfigProvider $configProvider
     * @param ClientDataManager $clientDataManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        AuthenticationManagerInterface $decoratedManager,
        DecoratedTokenFactoryInterface $tokenFactory,
        TFAConfigProvider $configProvider,
        ClientDataManager $clientDataManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->decoratedManager = $decoratedManager;
        $this->tokenFactory = $tokenFactory;
        $this->configProvider = $configProvider;
        $this->clientDataManager = $clientDataManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $token = $this->decoratedManager->authenticate($token);
        if (!$this->supportsToken($token)) {
            return $token;
        }

        if ($this->configProvider->isTFADisabled()) {
            return $token;
        }

        if ($this->configProvider->isCodeRequiredPerDevice() &&
            $this->clientDataManager->isClientRecognized($token->getUser())
        ) {
            return $token;
        }

        $twoFactorToken = $this->tokenFactory->create($token);

        $this->dispatcher->dispatch(
            TwoFactorAuthenticationEvents::START,
            new TwoFactorAuthenticationEvent($twoFactorToken)
        );

        return $twoFactorToken;
    }

    /**
     * Check if two factor authentication should be applied to $token
     *
     * @param TokenInterface $token
     * @return bool
     */
    protected function supportsToken(TokenInterface $token)
    {
        return $token instanceof ProUsernamePasswordOrganizationToken;
    }
}
