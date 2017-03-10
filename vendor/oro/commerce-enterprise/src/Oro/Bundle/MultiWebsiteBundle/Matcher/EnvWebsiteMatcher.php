<?php

namespace Oro\Bundle\MultiWebsiteBundle\Matcher;

class EnvWebsiteMatcher extends AbstractWebsiteMatcher
{
    /**
     * {@inheritdoc}
     */
    public function match()
    {
        $request = $this->requestStack->getMasterRequest();
        if ($request) {
            $varNames = $this->getConfigValues('oro_multiwebsite.matcher_env_var');
            $varValues = $this->getConfigValues('oro_multiwebsite.matcher_env_value');

            foreach ($varNames as $websiteId => $name) {
                if ($request->server->has($name)) {
                    if (array_key_exists($websiteId, $varValues)) {
                        if ($request->server->get($name) == $varValues[$websiteId]) {
                            return $this->getWebsiteReference($websiteId);
                        }
                    } else {
                        return $this->getWebsiteReference($websiteId);
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
        return 'oro.multiwebsite.matcher.env.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip()
    {
        return 'oro.multiwebsite.matcher.env.tooltip';
    }
}
