<?php

namespace Oro\Bundle\MultiWebsiteBundle\Matcher;

class CookieWebsiteMatcher extends AbstractWebsiteMatcher
{
    /**
     * {@inheritdoc}
     */
    public function match()
    {
        $request = $this->requestStack->getMasterRequest();
        if ($request) {
            $cookieName = $this->getConfigValue('oro_multiwebsite.website_cookie_name');

            if ($cookieName) {
                $cookieValue = $request->cookies->get($cookieName);
                if ($cookieValue) {
                    $cookieValues = $this->getConfigValues('oro_multiwebsite.website_cookie_value');

                    $id = array_search($cookieValue, $cookieValues, true);
                    if ($id) {
                        return $this->getWebsiteReference($id);
                    }
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.multiwebsite.matcher.cookie.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip()
    {
        return 'oro.multiwebsite.matcher.cookie.tooltip';
    }
}
