<?php

namespace Oro\Bundle\WarehouseBundle\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Oro\Bundle\WarehouseBundle\Entity\Warehouse;
use Oro\Bundle\WarehouseBundle\Provider\WarehouseAddressProvider;

class WarehouseFormViewListener
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var RequestStack */
    protected $requestStack;

    /** @var WarehouseAddressProvider */
    protected $warehouseAddressProvider;

    /**
     * @param TranslatorInterface $translator
     * @param DoctrineHelper $doctrineHelper
     * @param RequestStack $requestStack
     * @param WarehouseAddressProvider $warehouseAddressProvider
     */
    public function __construct(
        TranslatorInterface $translator,
        DoctrineHelper $doctrineHelper,
        RequestStack $requestStack,
        WarehouseAddressProvider $warehouseAddressProvider
    ) {
        $this->translator = $translator;
        $this->doctrineHelper = $doctrineHelper;
        $this->warehouseAddressProvider = $warehouseAddressProvider;
        $this->requestStack = $requestStack;
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onWarehouseView(BeforeListRenderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $warehouseId = (int)$request->get('id');
        if (!$warehouseId) {
            return;
        }

        /** @var Warehouse $warehouse */
        $warehouse = $this->doctrineHelper->getEntityReference('OroWarehouseBundle:Warehouse', $warehouseId);
        if (!$warehouse) {
            return;
        }

        $shippingOrigin = $this->warehouseAddressProvider->getShippingOriginByWarehouse($warehouse);

        if ($shippingOrigin->isEmpty()) {
            return;
        }

        $template = $event->getEnvironment()->render(
            'OroWarehouseBundle:WarehouseAddress:warehouse_address_view.html.twig',
            ['entity' => $shippingOrigin]
        );
        $this->addBlock($event->getScrollData(), $template, 'oro.warehouse.sections.address');
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onWarehouseEdit(BeforeListRenderEvent $event)
    {
        $template = $event->getEnvironment()->render(
            'OroWarehouseBundle:WarehouseAddress:warehouse_address_update.html.twig',
            ['form' => $event->getFormView()]
        );
        $this->addBlock($event->getScrollData(), $template, 'oro.warehouse.sections.address');
    }

    /**
     * @param ScrollData $scrollData
     * @param string $html
     * @param string $label
     * @param int $priority
     */
    protected function addBlock(ScrollData $scrollData, $html, $label, $priority = 100)
    {
        $blockLabel = $this->translator->trans($label);
        $blockId    = $scrollData->addBlock($blockLabel, $priority);
        $subBlockId = $scrollData->addSubBlock($blockId);
        $scrollData->addSubBlockData($blockId, $subBlockId, $html);
    }
}
