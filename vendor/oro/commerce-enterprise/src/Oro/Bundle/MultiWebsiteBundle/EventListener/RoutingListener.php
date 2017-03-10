<?php

namespace Oro\Bundle\MultiWebsiteBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MultiWebsiteBundle\Matcher\PathWebsiteMatcher;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RoutingListener
{
    const CURRENT_WEBSITE = 'current_website';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    /**
     * @var WebsiteUrlResolver
     */
    protected $urlResolver;

    /**
     * @var FrontendHelper
     */
    protected $frontendHelper;

    /**
     * @var PathWebsiteMatcher
     */
    protected $pathWebsiteMatcher;

    /**
     * @param ConfigManager $configManager
     * @param WebsiteManager $websiteManager
     * @param WebsiteUrlResolver $websiteUrlResolver
     * @param FrontendHelper $frontendHelper
     * @param PathWebsiteMatcher $pathWebsiteMatcher
     */
    public function __construct(
        ConfigManager $configManager,
        WebsiteManager $websiteManager,
        WebsiteUrlResolver $websiteUrlResolver,
        FrontendHelper $frontendHelper,
        PathWebsiteMatcher $pathWebsiteMatcher
    ) {
        $this->websiteManager = $websiteManager;
        $this->configManager = $configManager;
        $this->urlResolver = $websiteUrlResolver;
        $this->frontendHelper = $frontendHelper;
        $this->pathWebsiteMatcher = $pathWebsiteMatcher;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$this->isSupported($event)) {
            return;
        }

        $request = $event->getRequest();

        /** @var Website $website */
        $website = $request->attributes->get(
            self::CURRENT_WEBSITE,
            $this->websiteManager->getCurrentWebsite()
        );
        if (!$website || !$this->configManager->get('oro_multiwebsite.enable_redirect')) {
            return;
        }

        $websiteByPath = $this->pathWebsiteMatcher->match();
        if ($websiteByPath && $websiteByPath->getId() === $website->getId()) {
            return;
        }

        $redirectUrl = $this->getRedirectUrl($request, $website, $websiteByPath);
        if ($redirectUrl) {
            $response = new RedirectResponse($redirectUrl);
            $event->setResponse($response);

            return;
        }
    }

    /**
     * @param GetResponseEvent $event
     * @return bool
     */
    protected function isSupported(GetResponseEvent $event)
    {
        return $event->isMasterRequest()
            && $this->frontendHelper->isFrontendRequest()
            && !$event->getResponse() instanceof RedirectResponse;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getCleanUrl($url)
    {
        return rtrim(explode('?', $url)[0], '/');
    }

    /**
     * @param string $url
     * @param Website $website
     * @return bool
     */
    protected function isSecureUrl($url, Website $website)
    {
        return 0 === strpos($url, $this->configManager->get('oro_website.secure_url', false, false, $website));
    }

    /**
     * @param Request $request
     * @param Website $website
     * @param Website|null $websiteByPath
     * @return null|string
     */
    protected function getRedirectUrl(Request $request, Website $website, Website $websiteByPath = null)
    {
        $redirectUrl = null;
        $requestUri = $request->getUri();

        $websiteUrl = $this->getCleanUrl($this->urlResolver->getWebsiteUrl($website));
        if (!$websiteByPath) {
            if ($websiteUrl && false === strpos($requestUri, $websiteUrl)) {
                $redirectUrl = $websiteUrl;
            }
        } else {
            if ($this->isSecureUrl($requestUri, $websiteByPath)) {
                $websiteSecureUrl = $this->getCleanUrl($this->urlResolver->getWebsiteSecureUrl($website));
                $websiteByPathSecureUrl = $this->getCleanUrl($this->urlResolver->getWebsiteSecureUrl($websiteByPath));
                if ($websiteSecureUrl !== $websiteByPathSecureUrl) {
                    $redirectUrl = $websiteSecureUrl;
                }
            } else {
                $websiteByPathUrl = $this->getCleanUrl($this->urlResolver->getWebsiteUrl($websiteByPath));
                if ($websiteUrl !== $websiteByPathUrl) {
                    $redirectUrl = $websiteUrl;
                }
            }

            if ($redirectUrl) {
                $path = str_replace($this->pathWebsiteMatcher->getMatchedUrl(), '', $requestUri);
                $redirectUrl = $redirectUrl . '/' . ltrim($path, '/');
            }
        }

        return $redirectUrl;
    }
}
