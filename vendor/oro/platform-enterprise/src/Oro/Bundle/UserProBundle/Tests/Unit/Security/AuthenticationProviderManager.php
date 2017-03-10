<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit;

use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityProBundle\Tokens\ProUsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Security\WsseToken;
use Oro\Bundle\UserProBundle\Manager\ClientDataManager;
use Oro\Bundle\UserProBundle\Manager\TwoFactorCodeManager;
use Oro\Bundle\UserProBundle\Provider\TFAConfigProvider;
use Oro\Bundle\UserProBundle\Security\AuthenticationProviderManager;
use Oro\Bundle\UserProBundle\Security\TwoFactorToken;
use Oro\Bundle\UserProBundle\Security\TwoFactorTokenFactory;

class AuthenticationProviderManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldDecorateOriginalToken()
    {
        $manager = $this->getAuthManager(false, false, false, 1);

        $authToken = $manager->authenticate($this->getToken());

        $this->assertInstanceOf(TwoFactorToken::class, $authToken);
    }

    public function testShouldReturnOriginalTokenWithUnsupportedClass()
    {
        $manager = $this->getAuthManager(false, false, false, 0);
        $originalToken = new WsseToken();

        $authToken = $manager->authenticate($originalToken);

        $this->assertSame($originalToken, $authToken);
    }

    public function testShouldReturnOriginalTokenWhenDisabled()
    {
        $manager = $this->getAuthManager(true, false, false, 0);
        $originalToken = $this->getToken();

        $authToken = $manager->authenticate($originalToken);

        $this->assertSame($originalToken, $authToken);
    }

    public function testShouldReturnOriginalTokenWithTrustedDevice()
    {
        $manager = $this->getAuthManager(false, true, true, 0);
        $originalToken = $this->getToken();

        $authToken = $manager->authenticate($originalToken);

        $this->assertSame($originalToken, $authToken);
    }

    /**
     * @return AuthenticationManagerInterface
     */
    private function getBaseAuthManager()
    {
        $manager = $this->getMockBuilder(AuthenticationManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->atLeastOnce())
            ->method('authenticate')
            ->will($this->returnArgument(0));

        return $manager;
    }

    /**
     * @return DecoratedTokenFactoryInterface
     */
    private function getTokenFactory()
    {
        return new TwoFactorTokenFactory($this->getConfigProvider());
    }

    /**
     * @param  bool $isDisabled
     * @param  bool $isPerDevice
     * @return TFAConfigProvider
     */
    private function getConfigProvider($isDisabled = false, $isPerDevice = false)
    {
        $provider = $this->getMockBuilder(TFAConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->any())
            ->method('isTFADisabled')
            ->willReturn($isDisabled);

        $provider->expects($this->any())
            ->method('isCodeRequiredPerDevice')
            ->willReturn($isPerDevice);

        $provider->expects($this->any())
            ->method('getCodeExpiryDate')
            ->willReturn(new \DateTime());

        return $provider;
    }

    /**
     * @param  int $isClientRecognized
     * @return ClientDataManager
     */
    private function getClientDataManager($isClientRecognized)
    {
        $manager = $this->getMockBuilder(ClientDataManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('isClientRecognized')
            ->willReturn($isClientRecognized);

        return $manager;
    }

    /**
     * @param  int $nbOfSendCalls
     * @return TwoFactorCodeManager
     */
    private function getCodeManager($nbOfSendCalls)
    {
        $manager = $this->getMockBuilder(TwoFactorCodeManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->exactly($nbOfSendCalls))
            ->method('send');

        return $manager;
    }

    /**
     * @param  bool           $isDisabled
     * @param  bool           $isPerDevice
     * @param  bool           $isClientRecognized
     * @param  int            $nbOfSendCalls
     *
     * @return AuthenticationProviderManager
     */
    private function getAuthManager(
        $isDisabled,
        $isPerDevice,
        $isClientRecognized,
        $nbOfSendCalls
    ) {
        return new AuthenticationProviderManager(
            $this->getBaseAuthManager(),
            $this->getTokenFactory(),
            $this->getConfigProvider($isDisabled, $isPerDevice),
            $this->getClientDataManager($isClientRecognized),
            $this->getCodeManager($nbOfSendCalls)
        );
    }

    /**
     * @return TokenInterface
     */
    private function getToken()
    {
        return new ProUsernamePasswordOrganizationToken(
            new User(),
            'pass123',
            'form-login',
            new Organization()
        );
    }
}
