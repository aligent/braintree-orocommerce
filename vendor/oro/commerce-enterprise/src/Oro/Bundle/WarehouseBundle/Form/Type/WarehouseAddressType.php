<?php

namespace Oro\Bundle\WarehouseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\AddressBundle\Form\EventListener\AddressCountryAndRegionSubscriber;

class WarehouseAddressType extends AbstractType
{
    const NAME = 'oro_warehouse_address';

    /** @var string */
    protected $dataClass;

    /** @var AddressCountryAndRegionSubscriber */
    protected $countryAndRegionSubscriber;

    /**
     * @param AddressCountryAndRegionSubscriber $eventListener
     */
    public function __construct(AddressCountryAndRegionSubscriber $eventListener)
    {
        $this->countryAndRegionSubscriber = $eventListener;
    }

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->countryAndRegionSubscriber);
        $builder
            ->add(
                'country',
                'oro_country',
                [
                    'label' => 'oro.warehouse_address.form.country.label',
                    'configs' => [
                        'allowClear' => false,
                        'placeholder' => 'oro.address.form.choose_country'
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'region',
                'oro_region',
                [
                    'label' => 'oro.warehouse_address.form.region.label',
                    'configs' => [
                        'allowClear' => false,
                        'placeholder' => 'oro.address.form.choose_region'
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'postalCode',
                'text',
                [
                    'label' => 'oro.warehouse_address.form.postal_code.label',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'oro.warehouse_address.form.postal_code.label'
                    ]
                ]
            )
            ->add(
                'city',
                'text',
                [
                    'label' => 'oro.warehouse_address.form.city.label',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'oro.warehouse_address.form.city.label'
                    ]
                ]
            )
            ->add(
                'street',
                'text',
                [
                    'label' => 'oro.warehouse_address.form.street.label',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'oro.warehouse_address.form.street.label'
                    ]
                ]
            )
            ->add(
                'street2',
                'text',
                [
                    'required' => false,
                    'label' => 'oro.warehouse_address.form.street2.label',
                    'attr' => [
                        'placeholder' => 'oro.warehouse_address.form.street2.label'
                    ]
                ]
            )
            ->add(
                'region_text',
                'hidden',
                [
                    'required' => false,
                    'random_id' => true,
                    'label' => 'oro.warehouse_address.form.region_text.label',
                    'attr' => [
                        'placeholder' => 'oro.warehouse_address.form.region_text.label'
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'intention' => 'warehouse_address'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
