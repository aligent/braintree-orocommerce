<?php

namespace Oro\Bundle\OrganizationProBundle\Twig;

use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Component\Routing\Router;

use Oro\Bundle\WindowsBundle\Entity\AbstractWindowsState;
use Oro\Bundle\WindowsBundle\Twig\WindowsExtension as BaseWindowsExtension;

use Oro\Bundle\OrganizationProBundle\Exception\OrganizationAwareException;

/**
 * Override for render two step dialog form
 *
 * Class WindowsExtension
 * @package Oro\Bundle\OrganizationProBundle\Twig
 */
class WindowsExtension extends BaseWindowsExtension
{
    /** @var Router */
    protected $router;

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * { @inheritdoc }
     */
    public function renderFragment(\Twig_Environment $environment, AbstractWindowsState $windowState)
    {
        try {
            return parent::renderFragment($environment, $windowState);
        } catch (OrganizationAwareException $e) {
            $windowState->setRenderedSuccessfully(false);

            $uri = $this->router->generate(
                'oro_organizationpro_selector_form',
                ['form_url' => $this->windowsStateRequestManager->getUri($windowState->getData())]
            );

            /** @var HttpKernelExtension $httpKernelExtension */
            $httpKernelExtension = $environment->getExtension('http_kernel');
            $windowState->setRenderedSuccessfully(true);

            return $httpKernelExtension->renderFragment($uri);
        }
    }
}
