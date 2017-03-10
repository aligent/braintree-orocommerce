<?php

namespace Oro\Bundle\WarehouseBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\WarehouseBundle\Entity\Warehouse;
use Oro\Bundle\WarehouseBundle\Entity\WarehouseAddress;
use Oro\Bundle\WarehouseBundle\Provider\WarehouseAddressProvider;

class WarehouseType extends AbstractType
{
    const NAME = 'oro_warehouse';

    /** @var  string */
    protected $dataClass;

    /** @var WarehouseAddressProvider */
    protected $warehouseAddressProvider;

    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param WarehouseAddressProvider $warehouseAddressProvider
     * @param ManagerRegistry $registry
     */
    public function __construct(WarehouseAddressProvider $warehouseAddressProvider, ManagerRegistry $registry)
    {
        $this->warehouseAddressProvider = $warehouseAddressProvider;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'required' => true,
                    'label' => 'oro.warehouse.name.label'
                ]
            )
            ->add(
                'warehouse_address',
                WarehouseAddressType::NAME,
                [
                    'mapped' => false,
                    'label' => 'oro.warehouse.sections.address'
                ]
            );

        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'           => $this->dataClass,
            'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"'
        ]);
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
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * @param FormEvent $formEvent
     */
    public function onPostSetData(FormEvent $formEvent)
    {
        $data = $formEvent->getData();
        if (!$data instanceof Warehouse) {
            return;
        }

        $warehouseAddress = $this->warehouseAddressProvider->getShippingOriginByWarehouse($data);
        $formEvent->getForm()->get('warehouse_address')->setData($warehouseAddress);
    }

    /**
     * @param FormEvent $formEvent
     */
    public function onPostSubmit(FormEvent $formEvent)
    {
        /** @var Warehouse|null $warehouse */
        $warehouse = $formEvent->getData();
        if (!$warehouse) {
            return;
        }

        $form = $formEvent->getForm();
        if (!$form->isValid()) {
            return;
        }

        /** @var WarehouseAddress $warehouseAddress */
        $warehouseAddress = $form->get('warehouse_address')->getData();
        $warehouseAddress = $warehouseAddress ? $warehouseAddress : new WarehouseAddress();
        $existingWarehouseAddress = $this->getShippingOriginWarehouse($warehouse);

        if ($warehouseAddress->isSystem()) {
            if ($existingWarehouseAddress) {
                $this->getWarehouseAddressManager()->remove($existingWarehouseAddress);
            }
        } else {
            if (!$existingWarehouseAddress) {
                $existingWarehouseAddress = $this->createShippingOriginWarehouse($warehouse);
            }

            $existingWarehouseAddress->importAddress($warehouseAddress);
        }
    }

    /**
     * @param Warehouse $warehouse
     * @return WarehouseAddress|null
     */
    protected function getShippingOriginWarehouse(Warehouse $warehouse)
    {
        if (!$warehouse->getId()) {
            return null;
        }

        return $this->getWarehouseAddressRepository()->findOneBy(['warehouse' => $warehouse]);
    }

    /**
     * @param Warehouse $warehouse
     * @return WarehouseAddress
     */
    protected function createShippingOriginWarehouse(Warehouse $warehouse)
    {
        $manager = $this->getWarehouseAddressManager();

        $shippingOriginWarehouse = new WarehouseAddress();
        $shippingOriginWarehouse->setWarehouse($warehouse);

        $manager->persist($shippingOriginWarehouse);

        return $shippingOriginWarehouse;
    }

    /**
     * @return ObjectManager
     */
    protected function getWarehouseAddressManager()
    {
        return $this->registry->getManagerForClass('OroWarehouseBundle:WarehouseAddress');
    }

    /**
     * @return ObjectRepository
     */
    protected function getWarehouseAddressRepository()
    {
        return $this
            ->getWarehouseAddressManager()
            ->getRepository('OroWarehouseBundle:WarehouseAddress');
    }
}
