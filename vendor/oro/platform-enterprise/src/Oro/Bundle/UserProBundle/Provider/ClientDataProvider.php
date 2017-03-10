<?php

namespace Oro\Bundle\UserProBundle\Provider;

use DeviceDetector\Parser\Client\Browser;
use DeviceDetector\Parser\OperatingSystem;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides human readable data for the user agent of master (first) request
 */
class ClientDataProvider
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var string */
    protected $browser;

    /** @var string */
    protected $platform;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return string
     */
    public function getBrowser()
    {
        if (null === $this->browser) {
            $browserDetector = new Browser($this->getUserAgent());
            $browserData = $browserDetector->parse();
            $this->browser = (null !== $browserData) ? $browserData['name'] : 'Unknown';
        }

        return $this->browser;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        if (null === $this->platform) {
            $osDetector = new OperatingSystem($this->getUserAgent());
            $osData = $osDetector->parse();
            $this->platform = (null !== $osData) ? $osData['name'] : 'Unknown';
        }

        return $this->platform;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->getRequest()->getClientIp();
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->getRequest()->headers->get('user-agent');
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->requestStack->getMasterRequest();
    }
}
