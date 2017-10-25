<?php

namespace Entrepids\Bundle\BraintreeBundle\Method\Factory;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Braintree;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Symfony\Component\Routing\RouterInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Provider\ExtractOptionsProvider;
use Oro\Bundle\PaymentBundle\Provider\SurchargeProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;


class BraintreePaymentMethodFactory implements BraintreePaymentMethodFactoryInterface
{
   
	/**
	 * @var RouterInterface
	 */
	protected $router;
	
	/**
	 *
	 * @var BraintreeAdapter
	 */
	protected $adapter;
	
	/**
	 * @var DoctrineHelper
	 */
	protected $doctrineHelper;
	
	/**
	 * @var ExtractOptionsProvider
	 */
	protected $optionsProvider;
	
	/**
	 * @var PropertyAccessor
	 */
	protected $propertyAccessor;
	
	/**
	 * @var SurchargeProvider
	 */
	protected $surchargeProvider;

	/** @var Session */
	protected $session;
	
	/**
	 * 
	 * @var TranslatorInterface
	 */
	protected $translator;
	
	/**
	 * 
	 * @param DoctrineHelper $doctrineHelper
	 * @param PropertyAccessor $propertyAccessor
	 * @param Session $session
	 * @param TranslatorInterface $translator
	 */	
	public function __construct(DoctrineHelper $doctrineHelper, PropertyAccessor $propertyAccessor, Session $session, TranslatorInterface $translator) {
		$this->doctrineHelper = $doctrineHelper;
		$this->propertyAccessor = $propertyAccessor;
		$this->session = $session;
		$this->translator = $translator;
	}	
	
	/**
     * {@inheritdoc}
     */
    public function create(BraintreeConfigInterface $config)
    {
    	
        return new Braintree($config, $this->doctrineHelper, $this->propertyAccessor, $this->session, $this->translator);
    }
}
