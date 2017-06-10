<?php

namespace Entrepids\Bundle\BraintreeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;

class BraintreeSettingsRepository extends EntityRepository
{
	/**
	 * @param string $type
	 * @return BraintreeSettings[]
	 */
	public function getEnabledSettingsByType($type)
	{
		return $this->createQueryBuilder('settings')
		->innerJoin('settings.channel', 'channel')
		->andWhere('channel.enabled = true')
		->andWhere('channel.type = :type')
		->setParameter('type', $type)
		->getQuery()
		->getResult();
	}
}