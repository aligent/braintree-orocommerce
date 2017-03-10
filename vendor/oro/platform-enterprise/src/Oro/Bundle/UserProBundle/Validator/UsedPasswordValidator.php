<?php

namespace Oro\Bundle\UserProBundle\Validator;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use Oro\Bundle\UserProBundle\Entity\PasswordHistory;
use Oro\Bundle\UserProBundle\Validator\Constraints\UsedPassword;
use Oro\Bundle\UserProBundle\Provider\UsedPasswordConfigProvider;

class UsedPasswordValidator extends ConstraintValidator
{
    /** @var Registry */
    protected $registry;

    /** @var UsedPasswordConfigProvider */
    protected $configProvider;

    /** @var EncoderFactoryInterface */
    protected $encoderFactory;

    /**
     * @param Registry $registry
     * @param UsedPasswordConfigProvider $configProvider
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(
        Registry $registry,
        UsedPasswordConfigProvider $configProvider,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->registry = $registry;
        $this->configProvider = $configProvider;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($user, Constraint $constraint)
    {
        if (!$constraint instanceof UsedPassword) {
            throw new UnexpectedTypeException($constraint, UsedPassword::class);
        }

        if (!$this->configProvider->isUsedPasswordCheckEnabled()) {
            return;
        }

        $passwordHistoryLimit = $this->configProvider->getUsedPasswordsCheckNumber();

        $oldPasswords = $this->registry
            ->getManagerForClass('OroUserProBundle:PasswordHistory')
            ->getRepository('OroUserProBundle:PasswordHistory')
            ->findBy(['user' => $user], ['id' => 'DESC'], $passwordHistoryLimit);

        $encoder = $this->encoderFactory->getEncoder($user);
        $newPassword = $user->getPlainPassword();
        /** @var PasswordHistory $oldPassword */
        foreach ($oldPasswords as $oldPassword) {
            if ($encoder->isPasswordValid($oldPassword->getPasswordHash(), $newPassword, $oldPassword->getSalt())) {
                $this->context->addViolation($constraint->message);

                break;
            }
        }
    }
}
