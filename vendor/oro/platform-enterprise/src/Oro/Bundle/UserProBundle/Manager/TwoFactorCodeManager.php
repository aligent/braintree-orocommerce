<?php

namespace Oro\Bundle\UserProBundle\Manager;

use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserProBundle\Entity\AuthenticationCode;
use Oro\Bundle\UserProBundle\Entity\Repository\AuthenticationCodeRepository;
use Oro\Bundle\UserProBundle\Provider\TFAConfigProvider;

class TwoFactorCodeManager
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var TFAConfigProvider */
    protected $configProvider;

    /**
     * @param RegistryInterface $doctrine
     * @param TFAConfigProvider $configProvider
     */
    public function __construct(RegistryInterface $doctrine, TFAConfigProvider $configProvider)
    {
        $this->doctrine = $doctrine;
        $this->configProvider = $configProvider;
    }

    /**
     * @param AbstractUser $user
     *
     * @return AuthenticationCode
     */
    public function create(AbstractUser $user)
    {
        $this->invalidate($user);

        $authenticationCode = new AuthenticationCode();
        $authenticationCode->setUser($user);
        $authenticationCode->setCode($this->generateCode($user));
        $authenticationCode->setExpiresAt($this->configProvider->getCodeExpiryDate());

        $em = $this->getEntityManager();
        $em->persist($authenticationCode);
        $em->flush();

        return $authenticationCode;
    }

    /**
     * Find a valid authentication code for a given $user
     *
     * @param UserInterface $user
     *
     * @return string|null Returns null if no valid code is found
     */
    public function find(UserInterface $user)
    {
        if (!$user instanceof AbstractUser) {
            return null;
        }

        $expiresAfter = new \DateTime('now', new \DateTimeZone('UTC'));
        $code = $this->getAuthenticationCodeRepository()->getUserAuthenticationCode($user, $expiresAfter);
        if (!$code) {
            return null;
        }

        return $code->getCode();
    }

    /**
     * Invalidate all authentication codes of given $user
     *
     * @param UserInterface $user
     */
    public function invalidate(UserInterface $user)
    {
        if (!$user instanceof AbstractUser) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected instance of "%s", but "%s" given.',
                    AbstractUser::class,
                    get_class($user)
                )
            );
        }
        $this->getAuthenticationCodeRepository()->deleteUserAuthenticationCodes($user);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->doctrine->getManagerForClass(AuthenticationCode::class);
    }

    /**
     * @return AuthenticationCodeRepository
     */
    protected function getAuthenticationCodeRepository()
    {
        return $this->getEntityManager()
            ->getRepository(AuthenticationCode::class);
    }

    /**
     * @param  AbstractUser $user
     *
     * @return string
     */
    protected function generateCode(AbstractUser $user)
    {
        $codeLength = $this->configProvider->getCodeLength();

        return strtoupper(substr($user->generateToken(), 0, $codeLength));
    }
}
