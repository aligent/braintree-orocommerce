<?php
namespace Entrepids\Bundle\BraintreeBundle\Model\Adapter;

use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\PaymentMethodNonce;
use Braintree\Transaction;
use Braintree\Customer;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;

/**
 * Class BraintreeAdapter
 */
class BraintreeAdapter
{

    /**
     *
     * @var Config
     */
    private $config;

    /**
     *
     * @param Config $config
     */
    public function __construct(BraintreeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Initializes credentials.
     *
     * @return void
     */
    public function initCredentials()
    {
        $environmentSelected = $this->config->getAllowedEnvironmentTypes();
        if (strcmp($environmentSelected, 'Production') == 0 || strcmp($environmentSelected, 'production') == 0) {
            $this->environment('production');
        } else {
            $this->environment('sandbox');
        }
        $this->merchantId($this->config->getBoxMerchId());
        $this->publicKey($this->config->getBoxPublickKey());
        $this->privateKey($this->config->getBoxPrivateKey());
    }

    /**
     *
     * @param string|null $value
     * @return mixed
     */
    public function environment($value = null)
    {
        return Configuration::environment($value);
    }

    /**
     *
     * @param string|null $value
     * @return mixed
     */
    public function merchantId($value = null)
    {
        return Configuration::merchantId($value);
    }

    /**
     *
     * @param string|null $value
     * @return mixed
     */
    public function publicKey($value = null)
    {
        return Configuration::publicKey($value);
    }

    /**
     *
     * @param string|null $value
     * @return mixed
     */
    public function privateKey($value = null)
    {
        return Configuration::privateKey($value);
    }

    /**
     *
     * @param array $params
     * @return \Braintree\Result\Successful|\Braintree\Result\Error|null
     */
    public function generate(array $params = [])
    {
        try {
            return ClientToken::generate($params);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     *
     * @param array $attributes
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function sale(array $attributes)
    {
        return Transaction::sale($attributes);
    }

    /**
     *
     * @param string $token
     * @param array $attributes
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function creditCardsale($token, array $attributes)
    {
        return CreditCard::sale($token, $attributes);
    }

    /**
     *
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function submitForSettlement($transactionId, $amount = null)
    {
        return Transaction::submitForSettlement($transactionId, $amount);
    }

    /**
     *
     * @param string $customerId
     * @param unknown $customerId
     */
    public function findCustomer($customerId)
    {
        return Customer::find($customerId);
    }
}
