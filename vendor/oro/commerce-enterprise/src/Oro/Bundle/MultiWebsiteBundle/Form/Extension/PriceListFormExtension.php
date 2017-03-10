<?php

namespace Oro\Bundle\MultiWebsiteBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\PricingBundle\Entity\PriceListWebsiteFallback;
use Oro\Bundle\PricingBundle\Form\Type\PriceListCollectionType;
use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteType;
use Oro\Bundle\MultiWebsiteBundle\EventListener\PriceListListener;

class PriceListFormExtension extends AbstractTypeExtension
{
    const PRICE_LISTS_TO_WEBSITE_FIELD = 'priceList';
    const PRICE_LISTS_FALLBACK_FIELD = 'fallback';

    /**
     * @var string
     */
    protected $priceListToWebsiteClass;

    /**
     * @var PriceListListener
     */
    protected $listener;

    /**
     * @param string $priceListToWebsiteClass
     * @param PriceListListener $listener
     */
    public function __construct(
        $priceListToWebsiteClass,
        PriceListListener $listener
    ) {
        $this->listener = $listener;
        $this->priceListToWebsiteClass = $priceListToWebsiteClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                self::PRICE_LISTS_TO_WEBSITE_FIELD,
                PriceListCollectionType::NAME,
                [
                    'allow_add_after' => false,
                    'allow_add' => true,
                    'required' => false,
                    'options' => [
                        'data_class' => $this->priceListToWebsiteClass
                    ]
                ]
            )
            ->add(
                self::PRICE_LISTS_FALLBACK_FIELD,
                'choice',
                [
                    'label' => 'oro.pricing.fallback.label',
                    'mapped' => false,
                    'choices' => [
                        PriceListWebsiteFallback::CONFIG =>
                            'oro.pricing.fallback.config.label',
                        PriceListWebsiteFallback::CURRENT_WEBSITE_ONLY =>
                            'oro.pricing.fallback.current_website_only.label',
                    ],
                ]
            );

        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this->listener, 'onPostSetData']);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return WebsiteType::NAME;
    }
}
