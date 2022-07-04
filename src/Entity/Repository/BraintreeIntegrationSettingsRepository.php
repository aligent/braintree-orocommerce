<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
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
