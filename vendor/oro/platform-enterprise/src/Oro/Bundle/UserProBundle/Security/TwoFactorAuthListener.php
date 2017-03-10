<?php

namespace Oro\Bundle\UserProBundle\Security;

use Psr\Log\LoggerInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class TwoFactorAuthListener extends AbstractAuthenticationListener
{
    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        HttpUtils $httpUtils,
        $providerKey,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        array $options = array(),
        LoggerInterface $logger = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        parent::__construct(
            $tokenStorage,
            $authenticationManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $successHandler,
            $failureHandler,
            $options,
            $logger,
            $dispatcher
        );

        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    protected function requiresAuthentication(Request $request)
    {
        $currentToken = $this->tokenStorage->getToken();

        return $currentToken instanceof TwoFactorToken;
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        $this->checkExpiration();

        if ($this->httpUtils->checkRequestPath($request, $this->options['check_path'])) {
            return $this->checkAuthentication($request);
        }

        if (!$this->httpUtils->checkRequestPath($request, $this->options['login_path'])) {
            return $this->httpUtils->createRedirectResponse($request, $this->options['login_path']);
        }

        return null;
    }

    /**
     * Check two factor token is expired and throw exception
     *
     * @throws CustomUserMessageAuthenticationException
     */
    protected function checkExpiration()
    {
       /** @var TwoFactorToken $token */
        $token = $this->tokenStorage->getToken();

        if ($token->isExpired()) {
            throw new CustomUserMessageAuthenticationException('oro.userpro.two_factor.expired_token');
        }
    }

    /**
     * Check if provided credentials are valid. Clear token storage in case it's invalid.
     *
     * @param Request $request
     *
     * @return TokenInterface
     * @throws AuthenticationException
     */
    protected function checkAuthentication(Request $request)
    {
        $credentials = $request->get($this->options['auth_parameter']);
        $token = $this->tokenStorage->getToken();

        /** @var TwoFactorToken $token */
        $token->setCredentials($credentials);

        try {
            $authenticatedToken = $this->authenticationManager->authenticate($token);

            // unboxing TwoFactorToken
            if ($authenticatedToken instanceof TwoFactorToken) {
                $authenticatedToken = $authenticatedToken->getAuthenticatedToken();
            }

            return $authenticatedToken;
        } catch (AuthenticationException $e) {
            // reset the token to restart the authentication process
            $this->tokenStorage->setToken(null);

            throw $e;
        }
    }
}
