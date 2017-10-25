<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Config\Factory;

use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;

interface BraintreeConfigFactoryInterface
{
	/**
	 * @param BraintreeSettings $settings
	 * @return BraintreeConfigInterface
	 */
	public function create(BraintreeSettings $settings);
}
