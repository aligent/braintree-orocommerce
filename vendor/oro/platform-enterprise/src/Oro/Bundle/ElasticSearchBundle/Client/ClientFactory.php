<?php

namespace Oro\Bundle\ElasticSearchBundle\Client;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ClientFactory
{
    const OPTION_HOSTS = 'hosts';
    const OPTION_SSL_VERIFICATION = 'sslVerification';
    const OPTION_SSL_CERT = 'sslCert';
    const OPTION_SSL_KEY = 'sslKey';

    /**
     * @var ClientBuilder
     */
    private $builder;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * @param ClientBuilder $builder
     * @param PropertyAccessor $accessor
     */
    public function __construct(ClientBuilder $builder, PropertyAccessor $accessor)
    {
        $this->builder = $builder;
        $this->accessor = $accessor;
    }

    /**
     * @param array $configuration
     * @return Client
     * @throws \InvalidArgumentException
     */
    public function create(array $configuration = [])
    {
        if (!isset($configuration[self::OPTION_HOSTS])) {
            throw new \InvalidArgumentException('Hosts configuration option is required');
        }

        $this->builder->setHosts($configuration[self::OPTION_HOSTS]);
        unset($configuration[self::OPTION_HOSTS]);

        if (isset($configuration[self::OPTION_SSL_VERIFICATION])) {
            $this->builder->setSSLVerification($configuration[self::OPTION_SSL_VERIFICATION]);
            unset($configuration[self::OPTION_SSL_VERIFICATION]);
        }

        if (isset($configuration[self::OPTION_SSL_KEY])) {
            $this->assertArrayOfTwoElements(self::OPTION_SSL_KEY, $configuration[self::OPTION_SSL_KEY]);
            list($key, $keyPassword) = $configuration[self::OPTION_SSL_KEY];
            $this->builder->setSSLKey($key, $keyPassword);
            unset($configuration[self::OPTION_SSL_KEY]);
        }

        if (isset($configuration[self::OPTION_SSL_CERT])) {
            $this->assertArrayOfTwoElements(self::OPTION_SSL_CERT, $configuration[self::OPTION_SSL_CERT]);
            list($cert, $certPassword) = $configuration[self::OPTION_SSL_CERT];
            $this->builder->setSSLCert($cert, $certPassword);
            unset($configuration[self::OPTION_SSL_CERT]);
        }

        $this->assignConfigurationOptions($configuration);

        return $this->builder->build();
    }

    /**
     * @param string $optionName
     * @param mixed $optionValue
     * @throws \InvalidArgumentException
     */
    protected function assertArrayOfTwoElements($optionName, $optionValue)
    {
        if (false === is_array($optionValue) || count($optionValue) !== 2) {
            throw new \InvalidArgumentException(
                sprintf('Option %s has to be array of two elements', $optionName)
            );
        }
    }

    /**
     * @param array $configuration
     * @throws \InvalidArgumentException
     */
    protected function assignConfigurationOptions(array $configuration)
    {
        foreach ($configuration as $name => $value) {
            if (!$this->accessor->isWritable($this->builder, $name)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unsupported option %s with value %s',
                        $name,
                        $value
                    )
                );
            }

            $this->accessor->setValue($this->builder, $name, $value);
        }
    }
}
