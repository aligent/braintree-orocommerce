<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 1:44 AM
 */

namespace Aligent\BraintreeBundle\Entity\Repository;


use Aligent\BraintreeBundle\Entity\BraintreeIntegrationSettings;
use Doctrine\ORM\EntityRepository;

class BraintreeIntegrationSettingsRepository extends EntityRepository
{
    /**
     * @return BraintreeIntegrationSettings[]
     */
    public function getEnabledSettings()
    {
        return $this->createQueryBuilder('settings')
            ->innerJoin('settings.channel', 'channel')
            ->andWhere('channel.enabled = true')
            ->getQuery()
            ->getResult();
    }
}