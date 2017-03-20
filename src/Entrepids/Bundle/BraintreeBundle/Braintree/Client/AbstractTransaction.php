<?php

namespace Entrepids\Bundle\BraintreeBundle\Braintree\Client;

use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Guzzle\Http\ClientInterface as HTTPClientInterface;

/**
 * Class AbstractTransaction
 */
abstract class AbstractTransaction
{

	/** @var HTTPClientInterface */
	protected $httpClient;	
    /**
     * @var BraintreeAdapter
     */
    protected $adapter;

    /**
     * Constructor
     *
     * @param HTTPClientInterface $httpClient
     * @param BraintreeAdapter $adapter
     */
    public function __construct(HTTPClientInterface $httpClient, BraintreeAdapter $adapter)
    {
        $this->adapter = $adapter;
        $this->httpClient = $httpClient;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(array $data)
    {
        //$data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            //throw new Exception($message);
        } finally {
            $log['response'] = (array) $response['object'];
        }

        return $response;
    }

    /**
     * Process http request
     * @param array $data
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    abstract protected function process(array $data);
}
