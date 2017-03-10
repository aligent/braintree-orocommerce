<?php

namespace Oro\Bundle\WarehouseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Oro\Bundle\InventoryBundle\Controller\InventoryLevelController as BaseController;
use Oro\Bundle\InventoryBundle\Form\Type\InventoryLevelGridType;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\WarehouseBundle\Entity\Repository\WarehouseRepository;
use Oro\Bundle\WarehouseBundle\Form\Handler\InventoryLevelHandler;
use Oro\Bundle\SecurityBundle\Annotation\Acl;

class InventoryLevelController extends BaseController
{
    /**
     * Edit product inventory levels
     *
     * @Route("/update/{id}", name="oro_inventory_level_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_product_inventory_update",
     *      type="entity",
     *      class="OroInventoryBundle:InventoryLevel",
     *      permission="EDIT"
     * )
     *
     * @param Product $product
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function updateAction(Product $product, Request $request)
    {
        if (!$this->get('oro_security.security_facade')->isGranted('EDIT', $product)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(
            InventoryLevelGridType::NAME,
            null,
            ['product' => $product]
        );

        $handler = new InventoryLevelHandler(
            $form,
            $this->getDoctrine()->getManagerForClass('OroInventoryBundle:InventoryLevel'),
            $request,
            $this->get('oro_product.service.quantity_rounding')
        );

        $result = $this->get('oro_form.model.update_handler')->handleUpdate(
            $product,
            $form,
            null,
            null,
            null,
            $handler
        );

        if ($result instanceof Response) {
            return $result;
        }

        return array_merge($result, $this->widgetNoDataReasonsCheck($product));
    }

    /**
     * {@inheritdoc}
     */
    private function widgetNoDataReasonsCheck(Product $product)
    {
        $noDataReason = '';
        if (0 === count($product->getUnitPrecisions())) {
            $noDataReason = 'oro.inventory.inventorylevel.error.units';
        } elseif (0 === $this->getAvailableWarehousesCount()) {
            $noDataReason = 'oro.warehouse.error.warehouses';
        }

        return $noDataReason
            ? ['noDataReason' => $this->get('translator')->trans($noDataReason)]
            : [];
    }

    /**
     * @return integer
     */
    private function getAvailableWarehousesCount()
    {
        $warehouseClass = $this->getParameter('oro_warehouse.entity.warehouse.class');
        /** @var WarehouseRepository $warehouseRepository */
        $warehouseRepository = $this
            ->getDoctrine()
            ->getManagerForClass($warehouseClass)
            ->getRepository($warehouseClass);

        return $warehouseRepository->countAll();
    }
}
