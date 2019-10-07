<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/2/19
 * Time: 11:47 PM
 */

namespace Aligent\BraintreeBundle\Braintree;


use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\Transaction;

class Gateway
{
    const PRODUCTION = 'production';
    const SANDBOX = 'sandbox';

    /**
     * @var BraintreeConfigInterface
     */
    public $config;

    /**
     * @var Gateway
     */
    private static $instance;

    /**
     * Gateway constructor.
     * @param BraintreeConfigInterface $config
     */
    public function __construct(BraintreeConfigInterface $config)
    {
        $this->config = $config;
        Configuration::merchantId($config->getMerchantId());
        Configuration::publicKey($config->getPublicKey());
        Configuration::privateKey($config->getPrivateKey());
        Configuration::environment($config->getEnvironment());
    }

    /**
     * @param BraintreeConfigInterface $config
     * @return Gateway
     */
    public static function getInstance(BraintreeConfigInterface $config)
    {
        return self::$instance = new self($config);
    }

    /**
     * Generate Braintree Authentication Token
     * @param array $params
     * @return string
     */
    public function getAuthToken($params = [])
    {
        return ClientToken::generate($params);
    }

    /**
     * Charge the payment nonce
     * @param array $attribs
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public function sale(array $attribs)
    {
        return Transaction::sale($attribs);
    }
}