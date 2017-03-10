<?php

namespace Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\DependencyInjection\CollectOnDeliveryExtension;

class CollectOnDeliveryBundle extends Bundle
{
	/**
	 * {@inheritdoc}
	 */
	public function getContainerExtension()
	{
		if (!$this->extension) {
			$this->extension = new CollectOnDeliveryExtension();
		}
	
		return $this->extension;
	}
}
