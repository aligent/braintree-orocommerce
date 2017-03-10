<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\Engine;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\NodesNamespace;

use Oro\Bundle\ElasticSearchBundle\Engine\ElasticPluginVerifier;

class ElasticPluginVerifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not determine ElasticSearch node configuration
     */
    public function testIrregularServerResponse()
    {
        $requiredPlugins = ['delete-by-query'];

        $nodes = $this->getMockBuilder(NodesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nodes->expects($this->once())
            ->method('info')
            ->willReturn(null);

        /** @var Client|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('nodes')
            ->willReturn($nodes);

        $verifier = new ElasticPluginVerifier($requiredPlugins);
        $verifier->assertPluginsInstalled($client);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not determine ElasticSearch node configuration
     */
    public function testIncompleteServerResponse()
    {
        $requiredPlugins = ['delete-by-query'];

        $nodes = $this->getMockBuilder(NodesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nodes->expects($this->once())
            ->method('info')
            ->willReturn(['key' => 'value']);

        /** @var Client|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('nodes')
            ->willReturn($nodes);

        $verifier = new ElasticPluginVerifier($requiredPlugins);
        $verifier->assertPluginsInstalled($client);
    }

    public function testCheckingOfPluginsInTheClient()
    {
        $requiredPlugins = ['delete-by-query'];

        $nodes = $this->getMockBuilder(NodesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nodes->expects($this->once())
            ->method('info')
            ->willReturn($this->getSingleNodePluginsInfoWithDelete());

        /** @var Client|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('nodes')
            ->willReturn($nodes);

        $verifier = new ElasticPluginVerifier($requiredPlugins);
        $verifier->assertPluginsInstalled($client);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ElasticSearch server configuration error at node mvl73VtFQ9aJX6fpjHvVfQ. Make sure the following plugins are installed for each node: delete-by-query
     */
    // @codingStandardsIgnoreEnd
    public function testNoPluginsInTheClient()
    {
        $requiredPlugins = ['delete-by-query'];

        $nodes = $this->getMockBuilder(NodesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nodes->expects($this->once())
            ->method('info')
            ->willReturn($this->getSingleNodeWithNoPlugins());

        /** @var Client|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('nodes')
            ->willReturn($nodes);

        $verifier = new ElasticPluginVerifier($requiredPlugins);
        $verifier->assertPluginsInstalled($client);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ElasticSearch server configuration error at node 93225cafe025. Make sure the following plugins are installed for each node: delete-by-query
     */
    // @codingStandardsIgnoreEnd
    public function testCheckingOfPluginsInTheClientFailure()
    {
        $requiredPlugins = ['delete-by-query'];

        $nodes = $this->getMockBuilder(NodesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nodes->expects($this->once())
            ->method('info')
            ->willReturn($this->getSingleNodePluginsInfoWithoutDelete());

        /** @var Client|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('nodes')
            ->willReturn($nodes);

        $verifier = new ElasticPluginVerifier($requiredPlugins);
        $verifier->assertPluginsInstalled($client);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ElasticSearch server configuration error at node c832jdbDXkd3. Make sure the following plugins are installed for each node: delete-by-query
     */
    // @codingStandardsIgnoreEnd
    public function testCheckingOfManyNodesAndBadPluginConfig()
    {
        $requiredPlugins = ['delete-by-query'];

        $nodes = $this->getMockBuilder(NodesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nodes->expects($this->once())
            ->method('info')
            ->willReturn($this->getSeveralNodesPluginsInfoMixed());

        /** @var Client|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('nodes')
            ->willReturn($nodes);

        $verifier = new ElasticPluginVerifier($requiredPlugins);
        $verifier->assertPluginsInstalled($client);
    }

    public function testCheckingOfManyNodesAndProperPluginConfig()
    {
        $requiredPlugins = ['delete-by-query', 'shield'];

        $nodes = $this->getMockBuilder(NodesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nodes->expects($this->once())
            ->method('info')
            ->willReturn($this->getSeveralNodesPluginsProperlyConfigured());

        /** @var Client|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('nodes')
            ->willReturn($nodes);

        $verifier = new ElasticPluginVerifier($requiredPlugins);
        $verifier->assertPluginsInstalled($client);
    }

    /**
     * @return array
     */
    private function getSingleNodeWithNoPlugins()
    {
        return [
            'nodes' => [
                'mvl73VtFQ9aJX6fpjHvVfQ' => [
                    'plugins' => []
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getSingleNodePluginsInfoWithDelete()
    {
        return [
            'nodes' => [
                'mvl73VtFQ9aJX6fpjHvVfQ' => [
                    'plugins' => [
                        [
                            'name' => 'license'
                        ],
                        [
                            'name' => 'shield'
                        ],
                        [
                            'name' => 'delete-by-query'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getSingleNodePluginsInfoWithoutDelete()
    {
        return [
            'nodes' => [
                '93225cafe025' => [
                    'plugins' => [
                        [
                            'name' => 'license'
                        ],
                        [
                            'name' => 'shield'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getSeveralNodesPluginsInfoMixed()
    {
        return [
            'nodes' => [
                'c832jdbDXkd3' => [
                    'plugins' => [
                        [
                            'name' => 'license'
                        ],
                        [
                            'name' => 'shield'
                        ]
                    ]
                ],
                'node02' => [
                    'plugins' => [
                        [
                            'name' => 'delete-by-query'
                        ],
                        [
                            'name' => 'shield'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getSeveralNodesPluginsProperlyConfigured()
    {
        return [
            'nodes' => [
                '9mdmCU93rkaSUW' => [
                    'plugins' => [
                        [
                            'name' => 'delete-by-query'
                        ],
                        [
                            'name' => 'shield'
                        ]
                    ]
                ],
                'node03' => [
                    'plugins' => [
                        [
                            'name' => 'delete-by-query'
                        ],
                        [
                            'name' => 'shield'
                        ]
                    ]
                ]
            ]
        ];
    }
}
