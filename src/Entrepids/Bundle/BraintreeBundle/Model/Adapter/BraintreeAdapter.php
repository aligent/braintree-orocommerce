<?php
namespace Entrepids\Bundle\BraintreeBundle\Model\Adapter;

use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Transaction;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Entrepids\Bundle\BraintreeBundle\Settings\DataProvider\BasicEnvironmentDataProvider;

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
        // TODO: JOH 22/11/19 I'm reasonably sure the only possible values of getAllowedEnvironmentTypes
        // _are_ the two constants, so this could probably be a straight assignment.
        //  getAllowedEnvironmentTypes is pretty poorly named since it returnes the selected environment.
        $environmentSelected = $this->config->getAllowedEnvironmentTypes();
        if ($environmentSelected == BasicEnvironmentDataProvider::PRODUCTION) {
            $this->environment(BasicEnvironmentDataProvider::PRODUCTION);
        } else {
            $this->environment(BasicEnvironmentDataProvider::SANDBOX);
        }
        $this->merchantId($this->config->getBoxMerchId());
        $this->publicKey($this->config->getBoxPublicKey());
        $this->privateKey($this->config->getBoxPrivateKey());
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
    
    /**
     *
     * @param string|null $value
     * @return mixed
     */
    private function merchantId($value = null)
    {
        return Configuration::merchantId($value);
    }
    
    /**
     *
     * @param string|null $value
     * @return mixed
     */
    private function publicKey($value = null)
    {
        return Configuration::publicKey($value);
    }
    
    /**
     *
     * @param string|null $value
     * @return mixed
     */
    private function privateKey($value = null)
    {
        return Configuration::privateKey($value);
    }
    
    /**
     *
     * @param string|null $value
     * @return mixed
     */
    private function environment($value = null)
    {
        return Configuration::environment($value);
    }
}
