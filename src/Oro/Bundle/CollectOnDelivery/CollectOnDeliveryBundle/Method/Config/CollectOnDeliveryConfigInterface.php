<?php

namespace Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\Method\Config;

use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;
use Oro\Bundle\PaymentBundle\Method\Config\CountryConfigAwareInterface;

interface CollectOnDeliveryConfigInterface extends
    PaymentConfigInterface,
    CountryConfigAwareInterface
{
	/**
	 * @return array
	 */
	public function getAllowedCreditCards();
}
