<?php

namespace Oro\Bundle\MultiWebsiteBundle\Matcher;

use Oro\Bundle\WebsiteBundle\Entity\Website;

class PathWebsiteMatcher extends AbstractWebsiteMatcher
{
    /**
     * @var Website|null
     */
    protected $matchedWebsite = null;

    /**
     * @var string
     */
    protected $matchedUrl;

    /**
     * {@inheritdoc}
     */
    public function match()
    {
        if (null !== $this->matchedWebsite) {
            return $this->matchedWebsite;
        }

        if (null === $this->requestStack->getMasterRequest()) {
            return null;
        }

        $website = $this->matchByRequest();
        $this->matchedWebsite = $website;

        return $website;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.multiwebsite.matcher.path.label';
    }

    /**
     * @return null|Website
     */
    protected function matchByRequest()
    {
        $request = $this->requestStack->getMasterRequest();
        if (null === $request) {
            return null;
        }
        $maxMatchLength = 0;
        $matchId = null;

        $requestUri = $request->getUri();
        $urls = $this->getConfigValues('oro_website.url');
        foreach ($urls as $websiteId => $url) {
            if (strlen($url) > $maxMatchLength && 0 === strpos($requestUri, $url)) {
                $matchId = $websiteId;
                $maxMatchLength = strlen($url);
                $this->matchedUrl = $url;
            }
        }

        $urls = $this->getConfigValues('oro_website.secure_url');
        foreach ($urls as $websiteId => $url) {
            if (strlen($url) > $maxMatchLength && 0 === strpos($requestUri, $url)) {
                $matchId = $websiteId;
                $maxMatchLength = strlen($url);
                $this->matchedUrl = $url;
            }
        }
        if ($matchId) {
            return $this->getWebsiteReference($matchId);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip()
    {
        return 'oro.multiwebsite.matcher.path.tooltip';
    }

    /**
     * @return string
     */
    public function getMatchedUrl()
    {
        return $this->matchedUrl;
    }

    /**
     * Method should be called to reset saved website
     */
    public function onClear()
    {
        $this->matchedWebsite = null;
    }
}
