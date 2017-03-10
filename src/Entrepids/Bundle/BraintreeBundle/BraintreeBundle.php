<?php

namespace Entrepids\Bundle\BraintreeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Entrepids\Bundle\BraintreeBundle\DependencyInjection\BraintreeExtension;

class BraintreeBundle extends Bundle
{
	/**
	 * {@inheritdoc}
	 */
	public function getContainerExtension()
	{
		if (!$this->extension) {
			$this->extension = new BraintreeExtension();
		}
	
		return $this->extension;
	}	
}
