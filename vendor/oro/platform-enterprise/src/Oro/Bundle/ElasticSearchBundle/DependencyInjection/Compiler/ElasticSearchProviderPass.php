<?php

namespace Oro\Bundle\ElasticSearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\ElasticSearchBundle\Client\ClientFactory;
use Oro\Bundle\ElasticSearchBundle\Engine\ElasticSearch;

class ElasticSearchProviderPass implements CompilerPassInterface
{
    /** @return string */
    public static function getEngineIndexNameKey()
    {
        return 'search_engine_index_name';
    }

    /** @return string */
    public static function getEngineParametersKey()
    {
        return 'oro_search.engine_parameters';
    }

    /** @return string */
    public static function getEngineNameKey()
    {
        return 'search_engine_name';
    }

    /** @return string */
    public static function getEngineHostKey()
    {
        return 'search_engine_host';
    }

    /** @return string */
    public static function getEnginePortKey()
    {
        return 'search_engine_port';
    }

    /** @return string */
    public static function getEngineUsernameKey()
    {
        return 'search_engine_username';
    }

    /** @return string */
    public static function getEnginePasswordKey()
    {
        return 'search_engine_password';
    }

    /** @return string */
    public static function getEngineSSLVerificationKey()
    {
        return 'search_engine_ssl_verification';
    }

    /** @return string */
    public static function getEngineSSLCertificateKey()
    {
        return 'search_engine_ssl_cert';
    }

    /** @return string */
    public static function getEngineSSLCertificatePasswordKey()
    {
        return 'search_engine_ssl_cert_password';
    }

    /** @return string */
    public static function getEngineSSLKeyKey()
    {
        return 'search_engine_ssl_key';
    }

    /** @return string */
    public static function getEngineSSLKeyPasswordKey()
    {
        return 'search_engine_ssl_key_password';
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->getParameter(static::getEngineNameKey()) !== ElasticSearch::ENGINE_NAME) {
            return;
        }

        $engineParameters = $container->getParameter(static::getEngineParametersKey());
        $engineParameters = $this->processIndexName($container, $engineParameters);
        $engineParameters = $this->processElasticSearchConnection($container, $engineParameters);
        $engineParameters = $this->addSSLParameters($container, $engineParameters);
        $container->setParameter(static::getEngineParametersKey(), $engineParameters);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $engineParameters
     * @return array
     */
    protected function processElasticSearchConnection(ContainerBuilder $container, array $engineParameters)
    {
        if (isset($engineParameters['client'][ClientFactory::OPTION_HOSTS])) {
            return $engineParameters;
        }

        // connection parameters
        $host = $container->getParameter(static::getEngineHostKey());
        $port = $container->getParameter(static::getEnginePortKey());
        $username = $container->getParameter(static::getEngineUsernameKey());
        $password = $container->getParameter(static::getEnginePasswordKey());

        if ($host) {
            if ($username) {
                $host = $this->addAuthenticationToHost(
                    $host,
                    $username,
                    $password
                );
            }

            if ($port) {
                $host .= ':' . $port;
            }

            $engineParameters['client'][ClientFactory::OPTION_HOSTS] = [$host];
        }

        return $engineParameters;
    }

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @return string
     */
    protected function addAuthenticationToHost($host, $username, $password)
    {
        $authPart = $username.':'.$password.'@';

        if (false === strpos($host, '://')) {
            $host = $authPart . $host;
        } else {
            $host = str_replace('://', '://' . $authPart, $host);
        }

        return $host;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $engineParameters
     * @return array
     */
    protected function addSSLParameters(ContainerBuilder $container, array $engineParameters)
    {
        $engineParameters = $this->addSSLVerificationParameter($container, $engineParameters);
        $engineParameters = $this->addSSLCertParameter($container, $engineParameters);
        $engineParameters = $this->addSSLKeyParameter($container, $engineParameters);

        return $engineParameters;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $engineParameters
     * @return array
     */
    protected function addSSLVerificationParameter(ContainerBuilder $container, array $engineParameters)
    {
        if (isset($engineParameters['client'][ClientFactory::OPTION_SSL_VERIFICATION])) {
            return $engineParameters;
        }

        if ($container->hasParameter(static::getEngineSSLVerificationKey())) {
            $sslVerification = $container->getParameter(static::getEngineSSLVerificationKey());

            if ($sslVerification) {
                $engineParameters['client'][ClientFactory::OPTION_SSL_VERIFICATION] = $sslVerification;
            }
        }

        return $engineParameters;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $engineParameters
     * @return array
     */
    protected function addSSLCertParameter(ContainerBuilder $container, array $engineParameters)
    {
        if (isset($engineParameters['client'][ClientFactory::OPTION_SSL_CERT])) {
            return $engineParameters;
        }

        if ($container->hasParameter(static::getEngineSSLCertificateKey())) {
            $sslCert = $container->getParameter(static::getEngineSSLCertificateKey());

            if ($sslCert) {
                $sslCertPassword = $container->hasParameter(static::getEngineSSLCertificatePasswordKey())
                    ? $container->getParameter(static::getEngineSSLCertificatePasswordKey())
                    : null;

                $engineParameters['client'][ClientFactory::OPTION_SSL_CERT] = [$sslCert, $sslCertPassword];
            }
        }

        return $engineParameters;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $engineParameters
     * @return array
     */
    protected function addSSLKeyParameter(ContainerBuilder $container, array $engineParameters)
    {
        if (isset($engineParameters['client'][ClientFactory::OPTION_SSL_KEY])) {
            return $engineParameters;
        }

        if ($container->hasParameter(static::getEngineSSLKeyKey())) {
            $sslKey = $container->getParameter(static::getEngineSSLKeyKey());

            if ($sslKey) {
                $sslKeyPassword = $container->hasParameter(static::getEngineSSLKeyPasswordKey())
                    ? $container->getParameter(static::getEngineSSLKeyPasswordKey())
                    : null;

                $engineParameters['client'][ClientFactory::OPTION_SSL_KEY] = [$sslKey, $sslKeyPassword];
            }
        }

        return $engineParameters;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $engineParameters
     * @return array
     */
    protected function processIndexName(ContainerBuilder $container, array $engineParameters)
    {
        if (!isset($engineParameters['index'])) {
            $engineParameters['index']['index'] = $container->getParameter(static::getEngineIndexNameKey());
        }

        if (!is_array($engineParameters['index'])) {
            throw new \RuntimeException(
                sprintf('ES engine parameter (%s.index) should be an array', static::getEngineParametersKey())
            );
        }

        if (!isset($engineParameters['index']['index'])) {
            throw new \RuntimeException(
                sprintf('ES engine parameter (%s.index.index) is required', static::getEngineParametersKey())
            );
        }

        return $engineParameters;
    }
}
