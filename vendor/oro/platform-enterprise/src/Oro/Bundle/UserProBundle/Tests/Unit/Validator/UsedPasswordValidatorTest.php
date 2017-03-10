<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserProBundle\Entity\PasswordHistory;
use Oro\Bundle\UserProBundle\Provider\UsedPasswordConfigProvider;
use Oro\Bundle\UserProBundle\Validator\Constraints\UsedPassword;
use Oro\Bundle\UserProBundle\Validator\UsedPasswordValidator;

class UsedPasswordValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var User */
    protected $user;

    /** @var UsedPassword */
    protected $constraint;

    /** @var UsedPasswordValidator */
    protected $validator;

    protected function setUp()
    {

        $this->user = new User();
        $this->user->setPassword('test123');
        $passHash1 = $this->createPasswordHistory($this->user);
        $passHash2 = $this->createPasswordHistory($this->user);

        $repo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findBy')
            ->willReturn([$passHash1, $passHash2]);

        $om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $om->expects($this->once())
            ->method('getRepository')
            ->willReturn($repo);

        $registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($om);

        $configProvider = $this->getMockBuilder(UsedPasswordConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configProvider->expects($this->once())
            ->method('isUsedPasswordCheckEnabled')
            ->willReturn(true);
        $configProvider->expects($this->once())
            ->method('getUsedPasswordsCheckNumber')
            ->willReturn(3);
        $encoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $encoder->expects($this->any())
            ->method('isPasswordValid')
            ->willReturn(true);

        $encoderFactory = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $encoderFactory->expects($this->once())
            ->method('getEncoder')
            ->willReturn($encoder);

        $this->validator = new UsedPasswordValidator($registry, $configProvider, $encoderFactory);
    }

    public function testValidateAlreadyUsedPassword()
    {
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->disableOriginalConstructor()->getMock();
        $context->expects($this->once())
            ->method('addViolation');
        $this->validator->initialize($context);
        $this->validator->validate($this->user, new UsedPassword());
    }

    private function createPasswordHistory(User $user)
    {
        $passwordHistory = new PasswordHistory();
        $passwordHistory->setUser($user);
        $passwordHistory->setSalt($user->getSalt());
        $passwordHistory->setPasswordHash($this->user->getPassword());

        return $passwordHistory;
    }
}
