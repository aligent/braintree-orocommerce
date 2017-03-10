<?php

namespace Oro\Bundle\ElasticSearchBundle\Engine;

use Elasticsearch\Client;

class ElasticPluginVerifier
{
    /**
     * @var array
     */
    protected $requiredPlugins;

    /**
     * @param array $requiredPlugins
     */
    public function __construct(array $requiredPlugins)
    {
        $this->requiredPlugins = $requiredPlugins;
    }

    /**
     * Performing the ES request and retrieving the plugin data info.
     * Checking response whether required plugins are installed.
     *
     * @param Client $client
     */
    public function assertPluginsInstalled(Client $client)
    {
        if (empty($this->requiredPlugins)) {
            return;
        }

        $request  = $this->getPluginsRequest();
        $response = $client->nodes()
            ->info($request);

        $this->checkResponse($response);
    }

    /**
     * Iterates through the response and checks for required plugins.
     *
     * @param $response
     */
    private function checkResponse($response)
    {
        if (empty($response) || !isset($response['nodes'])) {
            throw new \RuntimeException('Could not determine ElasticSearch node configuration');
        }

        foreach ($response['nodes'] as $nodeName => $node) {
            $installedPlugins = [];

            foreach ($node['plugins'] as $plugin) {
                $installedPlugins[] = $plugin['name'];
            }

            $installedPlugins = array_unique($installedPlugins);
            $installedRequiredPlugins = array_intersect($installedPlugins, $this->requiredPlugins);

            if (count($installedRequiredPlugins) !== count($this->requiredPlugins)) {
                $missingPlugins = implode(', ', $this->requiredPlugins);
                throw new \RuntimeException(
                    'ElasticSearch server configuration error at node ' .
                    $nodeName . '. ' .
                    'Make sure the following plugins are installed for each node: ' .
                    $missingPlugins . '.'
                );
            }
        }
    }

    /**
     * @return array
     */
    private function getPluginsRequest()
    {
        return ['metric' => 'plugins'];
    }
}
