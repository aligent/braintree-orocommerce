<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\ElasticSearchBundle\DependencyInjection\Compiler\ElasticSearchProviderPass;
use Oro\Bundle\ElasticSearchBundle\Engine\ElasticSearch;

class ElasticSearchProviderPassTest extends AbstractElasticSearchProviderPassTest
{
    public function setUp()
    {
        parent::setUp();

        $this->compiler = new ElasticSearchProviderPass();
    }

    protected function getEngineParametersKey()
    {
        return 'oro_search.engine_parameters';
    }

    /**
     * {@inheritdoc}
     */
    public function incorrectIndexParameterProvider()
    {
        return [
            'not an array' => [
                'parameters' => [
                    'index' => 'not an array',
                ],
                'message' => 'ES engine parameter (oro_search.engine_parameters.index) should be an array'
            ],
            'index parameter is not in array' => [
                'parameters' => [
                    'index' => ['body' => []],
                ],
                'message' => 'ES engine parameter (oro_search.engine_parameters.index.index) is required'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function processProvider()
    {
        $hostWithAuthInfo = sprintf(
            '%s:%s@%s:%s',
            self::DEFAULT_USERNAME,
            self::DEFAULT_PASSWORD,
            self::DEFAULT_HOST,
            self::DEFAULT_PORT
        );

        return [
            'Global configuration used only. Parameters empty' => [
                'hasParameters' => [],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [
                        'client' => [
                            'sslVerification' => '/cert/path',
                            'sslCert' => ['/path/to/cacert.pem', 'certPassword'],
                            'sslKey' => ['/path/to/key', 'keyPass'],
                            'hosts' => ['127.0.0.1', 'admin:admin@127.0.0.2']
                        ],
                        'index' => [
                            'index' => 'index_name',
                            'body' => [
                                'mappings' => []
                            ]
                        ]
                    ],
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'sslVerification' => '/cert/path',
                        'sslCert' => ['/path/to/cacert.pem', 'certPassword'],
                        'sslKey' => ['/path/to/key', 'keyPass'],
                        'hosts' => ['127.0.0.1', 'admin:admin@127.0.0.2']
                    ],
                    'index' => [
                        'index' => 'index_name',
                        'body' => [
                            'mappings' => []
                        ]
                    ]
                ]
            ],
            'empty global configuration. Parameters used' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => false,
                    'search_engine_ssl_cert' => false,
                    'search_engine_ssl_key' => false,
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                    'search_engine_host' => self::DEFAULT_HOST,
                    'search_engine_port' => self::DEFAULT_PORT,
                    'search_engine_username' => self::DEFAULT_USERNAME,
                    'search_engine_password' => self::DEFAULT_PASSWORD,
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => [$hostWithAuthInfo]
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ]
            ],
            'empty global configuration. Parameters with http host used' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => false,
                    'search_engine_ssl_cert' => false,
                    'search_engine_ssl_key' => false,
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                    'search_engine_host' => 'http://127.0.0.1',
                    'search_engine_port' => self::DEFAULT_PORT,
                    'search_engine_username' => self::DEFAULT_USERNAME,
                    'search_engine_password' => self::DEFAULT_PASSWORD,
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => ['http://username:1234567@127.0.0.1:9200']
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ]
            ],
            'global configuration has host settings. Only index name from parameters used' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => false,
                    'search_engine_ssl_cert' => false,
                    'search_engine_ssl_key' => false,
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [
                        'client' => [
                            'hosts' => ['someTestHost:port'],
                        ],
                    ],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => ['someTestHost:port'],
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ],
            ],
            'ssl verification option given' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => true,
                    'search_engine_ssl_cert' => false,
                    'search_engine_ssl_key' => false,
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                    'search_engine_host' => self::DEFAULT_HOST,
                    'search_engine_port' => self::DEFAULT_PORT,
                    'search_engine_username' => self::DEFAULT_USERNAME,
                    'search_engine_password' => self::DEFAULT_PASSWORD,
                    'search_engine_ssl_verification' => '/path/to/cacert.pem',
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => [$hostWithAuthInfo],
                        'sslVerification' => '/path/to/cacert.pem',
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ]
            ],
            'cert option with null value and password given' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => false,
                    'search_engine_ssl_cert' => true,
                    'search_engine_ssl_key' => false
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                    'search_engine_host' => self::DEFAULT_HOST,
                    'search_engine_port' => self::DEFAULT_PORT,
                    'search_engine_username' => self::DEFAULT_USERNAME,
                    'search_engine_password' => self::DEFAULT_PASSWORD,
                    'search_engine_ssl_cert' => null
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => [$hostWithAuthInfo],
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ]
            ],
            'cert option without password given' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => false,
                    'search_engine_ssl_cert' => true,
                    'search_engine_ssl_cert_password' => false,
                    'search_engine_ssl_key' => false
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                    'search_engine_host' => self::DEFAULT_HOST,
                    'search_engine_port' => self::DEFAULT_PORT,
                    'search_engine_username' => self::DEFAULT_USERNAME,
                    'search_engine_password' => self::DEFAULT_PASSWORD,
                    'search_engine_ssl_cert' => '/path/to/cacert.pem'
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => [$hostWithAuthInfo],
                        'sslCert' => ['/path/to/cacert.pem', null],
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ]
            ],
            'cert option with password given' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => false,
                    'search_engine_ssl_cert' => true,
                    'search_engine_ssl_cert_password' => true,
                    'search_engine_ssl_key' => false
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                    'search_engine_host' => self::DEFAULT_HOST,
                    'search_engine_port' => self::DEFAULT_PORT,
                    'search_engine_username' => self::DEFAULT_USERNAME,
                    'search_engine_password' => self::DEFAULT_PASSWORD,
                    'search_engine_ssl_cert' => '/path/to/cert',
                    'search_engine_ssl_cert_password' => 'certPassword'
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => [$hostWithAuthInfo],
                        'sslCert' => ['/path/to/cert', 'certPassword'],
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ]
            ],
            'key option with null value and password given' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => false,
                    'search_engine_ssl_cert' => false,
                    'search_engine_ssl_key' => true,
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                    'search_engine_host' => self::DEFAULT_HOST,
                    'search_engine_port' => self::DEFAULT_PORT,
                    'search_engine_username' => self::DEFAULT_USERNAME,
                    'search_engine_password' => self::DEFAULT_PASSWORD,
                    'search_engine_ssl_key' => null,
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => [$hostWithAuthInfo],
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ]
            ],
            'key option without password given' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => false,
                    'search_engine_ssl_cert' => false,
                    'search_engine_ssl_key' => true,
                    'search_engine_ssl_key_password' => false
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                    'search_engine_host' => self::DEFAULT_HOST,
                    'search_engine_port' => self::DEFAULT_PORT,
                    'search_engine_username' => self::DEFAULT_USERNAME,
                    'search_engine_password' => self::DEFAULT_PASSWORD,
                    'search_engine_ssl_key' => '/path/to/key'
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => [$hostWithAuthInfo],
                        'sslKey' => ['/path/to/key', null],
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ]
            ],
            'key option with password given' => [
                'hasParameters' => [
                    'search_engine_ssl_verification' => false,
                    'search_engine_ssl_cert' => false,
                    'search_engine_ssl_key' => true,
                    'search_engine_ssl_key_password' => true
                ],
                'getParameters' => [
                    'search_engine_name' => ElasticSearch::ENGINE_NAME,
                    'oro_search.engine_parameters' => [],
                    'search_engine_index_name' => self::DEFAULT_INDEX_NAME,
                    'search_engine_host' => self::DEFAULT_HOST,
                    'search_engine_port' => self::DEFAULT_PORT,
                    'search_engine_username' => self::DEFAULT_USERNAME,
                    'search_engine_password' => self::DEFAULT_PASSWORD,
                    'search_engine_ssl_key' => '/path/to/key',
                    'search_engine_ssl_key_password' => 'keyPassword'
                ],
                'expectedSearchConfiguration' => [
                    'client' => [
                        'hosts' => [$hostWithAuthInfo],
                        'sslKey' => ['/path/to/key', 'keyPassword'],
                    ],
                    'index' => [
                        'index' => self::DEFAULT_INDEX_NAME,
                    ],
                ]
            ],
        ];
    }
}
