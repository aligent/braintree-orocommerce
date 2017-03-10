<?php

namespace Oro\Bundle\UserProBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\UserBundle\Entity\AbstractUser;

class ClientDataRepository extends EntityRepository
{
    /**
     * @param  AbstractUser $user
     * @param  string       $ipAddress
     * @param  string       $userAgent
     * @return boolean
     */
    public function hasClient(AbstractUser $user, $ipAddress, $userAgent)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('COUNT(c)')
            ->where('c.user = :user')
            ->andWhere('c.ipAddress = :ipAddress')
            ->andWhere('c.userAgent = :userAgent')
            ->setParameters(
                [
                    'user' => $user,
                    'ipAddress' => $ipAddress,
                    'userAgent' => $userAgent,
                ]
            );

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }
}
