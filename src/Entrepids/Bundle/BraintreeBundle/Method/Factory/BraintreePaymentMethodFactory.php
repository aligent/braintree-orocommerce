<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Factory;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Entrepids\Bundle\BraintreeBundle\Method\EntrepidsBraintreeMethod;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Symfony\Component\Routing\RouterInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Provider\ExtractOptionsProvider;
use Oro\Bundle\PaymentBundle\Provider\SurchargeProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\Purchase\PurchaseData\PurchaseData;

class BraintreePaymentMethodFactory implements BraintreePaymentMethodFactoryInterface
{

    /**
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     *
     * @var BraintreeAdapter
     */
    protected $adapter;

    /**
     *
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     *
     * @var ExtractOptionsProvider
     */
    protected $optionsProvider;

    /**
     *
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     *
     * @var SurchargeProvider
     */
    protected $surchargeProvider;

    /**
     * @var Session
     */
    protected $session;

    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;
    
    /**
     *
     * @var PurchaseData
     */
    protected $purchaseData;

    /**
     *
     * @param DoctrineHelper $doctrineHelper
     * @param PropertyAccessor $propertyAccessor
     * @param Session $session
     * @param TranslatorInterface $translator
     * @param PurchaseData $purchaseData
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        PropertyAccessor $propertyAccessor,
        Session $session,
        TranslatorInterface $translator,
        PurchaseData $purchaseData
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->propertyAccessor = $propertyAccessor;
        $this->session = $session;
        $this->translator = $translator;
        $this->purchaseData = $purchaseData;
    }

    /**
     * This method is called when the Braintree method is selected in the checkout process
     *
     * {@inheritdoc}
     */
    public function create(BraintreeConfigInterface $config)
    {
        return new EntrepidsBraintreeMethod(
            $config,
            $this->doctrineHelper,
            $this->propertyAccessor,
            $this->session,
            $this->translator,
            $this->purchaseData
        );
    }
}
