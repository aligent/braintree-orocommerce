<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Entity\Manager;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserProBundle\Entity\AuthenticationCode;
use Oro\Bundle\UserProBundle\Entity\Repository\AuthenticationCodeRepository;
use Oro\Bundle\UserProBundle\Manager\TwoFactorCodeManager;
use Oro\Bundle\UserProBundle\Provider\TFAConfigProvider;
use Oro\Bundle\UserProBundle\Sender\TwoFactorCodeSender;

class TwoFactorCodeManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var TwoFactorCodeManager */
    protected $codeManager;

    /** @var TwoFactorCodeSender|\PHPUnit_Framework_MockObject_MockObject */
    protected $codeSender;

    /** @var  RegistryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $doctrine;

    /** @var User */
    protected $user;

    public function setUp()
    {
        $this->doctrine = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = new User();
        $this->codeManager = new TwoFactorCodeManager($this->doctrine, $this->getConfigProvider());
    }

    public function testCreate()
    {
        $this->getRepositoryMock();
        $authCode = $this->codeManager->create($this->user);

        $this->assertInstanceOf(AuthenticationCode::class, $authCode);
        $this->assertSame($this->user, $authCode->getUser());
    }

    /**
     * Test find an authentication code
     */
    public function testFind()
    {
        $code = new AuthenticationCode();
        $code->setCode('12345');
        $code->setUser($this->user);

        $repo = $this->getRepositoryMock();
        $repo->expects($this->once())
            ->method('getUserAuthenticationCode')
            ->with($this->user)
            ->willReturn($code);

        $this->assertSame($code->getCode(), $this->codeManager->find($this->user));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthenticationCodeRepository
     */
    private function getRepositoryMock()
    {
        $repo = $this->getMockBuilder(AuthenticationCodeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->any())
            ->method('getRepository')
            ->willReturn($repo);

        $this->doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($em);

        return $repo;
    }

    /**
     * @return TFAConfigProvider
     */
    private function getConfigProvider()
    {
        $provider = $this->getMockBuilder(TFAConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->any())
            ->method('getCodeLength')
            ->willReturn(6);

        $provider->expects($this->any())
            ->method('getCodeExpiryDate')
            ->willReturn(new \DateTime());

        return $provider;
    }
}
