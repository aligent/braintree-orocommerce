<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;

use Oro\Bundle\UserProBundle\Security\TwoFactorToken;
use Oro\Bundle\UserProBundle\Security\TwoFactorAuthListener;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;

class TwoFactorAuthListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test handle two factor authentication
     */
    public function testHandle()
    {
        $response = new Response();

        $tokenStorage =          $this->getMockForClass(TokenStorageInterface::class);
        $authenticationManager = $this->getMockForClass(AuthenticationManagerInterface::class);
        $sessionStrategy =       $this->getMockForClass(SessionAuthenticationStrategyInterface::class);
        $httpUtils =             $this->getMockForClass(HttpUtils::class);
        $successHandler =        $this->getMockForClass(AuthenticationSuccessHandlerInterface::class);
        $failureHandler =        $this->getMockForClass(AuthenticationFailureHandlerInterface::class);

        $providerKey = 'main';
        $options     = ['auth_parameter' => 'test'];

        $listener = new TwoFactorAuthListener(
            $tokenStorage,
            $authenticationManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $successHandler,
            $failureHandler,
            $options
        );

        $httpUtils
            ->expects($this->once())
            ->method('checkRequestPath')
            ->willReturn(true);

        $session = $this->getMockForClass(Session::class);

        $userPassAndOrgToken = $this->getMockForClass(UsernamePasswordOrganizationToken::class);

        $token = $this->getMockForClass(TwoFactorToken::class);
        $token
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(false);
        $token
            ->expects($this->once())
            ->method('setCredentials');
        $token
            ->expects($this->once())
            ->method('getAuthenticatedToken')
            ->willReturn($userPassAndOrgToken);

        $tokenStorage
            ->expects($this->atLeastOnce())
            ->method('getToken')
            ->willReturn($token);
        $tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with($userPassAndOrgToken);

        $authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($token);

        $request = $this->getMockForClass(Request::class);
        $request
            ->expects($this->once())
            ->method('hasSession')
            ->willReturn(true);
        $request
            ->expects($this->once())
            ->method('hasPreviousSession')
            ->willReturn(true);
        $request
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($session);

        $event = $this->getMockForClass(GetResponseEvent::class);
        $event->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);

        $event->expects($this->any())
            ->method('setResponse')
            ->with($response);

        $successHandler
            ->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->willReturn($response);

        $listener->handle($event);
    }

    /**
     * Get Mock for class with disabled constructor
     *
     * @param $class
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|$class
     */
    protected function getMockForClass($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
