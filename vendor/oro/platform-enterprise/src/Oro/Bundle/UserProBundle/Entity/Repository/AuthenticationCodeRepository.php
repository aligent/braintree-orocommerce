<?php

namespace Oro\Bundle\UserProBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\UserBundle\Entity\AbstractUser;

class AuthenticationCodeRepository extends EntityRepository
{
    /**
     * @param AbstractUser $user
     * @param \DateTime $expiresAfter Returns only code that expires after this date
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserAuthenticationCode(AbstractUser $user, \DateTime $expiresAfter)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb->select('ac')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('ac.user', ':user'),
                    $qb->expr()->gt('ac.expiresAt', ':expires')
                )
            )
            ->setParameters(
                [
                    'user' => $user,
                    'expires' => $expiresAfter,
                ]
            )
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param AbstractUser $user
     */
    public function deleteUserAuthenticationCodes(AbstractUser $user)
    {
        $qb = $this->createQueryBuilder('ac');

        $qb->delete()
            ->where($qb->expr()->eq('ac.user', ':user'))
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
