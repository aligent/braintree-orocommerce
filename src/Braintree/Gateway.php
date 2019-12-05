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
use Braintree\Customer;
use Braintree\Exception\NotFound;
use Braintree\Transaction;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class Gateway
{
    const PRODUCTION = 'production';
    const SANDBOX = 'sandbox';

    /**
     * @var BraintreeConfigInterface
     */
    public $config;

    /**
     * @var \Braintree\Gateway
     */
    protected $braintreeGateway;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * Gateway constructor.
     * @param BraintreeConfigInterface $config
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(BraintreeConfigInterface $config, DoctrineHelper $doctrineHelper)
    {
        $this->config = $config;
        $this->doctrineHelper = $doctrineHelper;
        $this->braintreeGateway = new \Braintree\Gateway(
            [
                'environment' => $config->getEnvironment(),
                'publicKey'   => $config->getPublicKey(),
                'privateKey'  => $config->getPrivateKey(),
                'merchantId'  => $config->getMerchantId()
            ]
        );
    }

    /**
     * Generate Braintree Authentication Token for a customer user
     * @param CustomerUser $customerUser
     * @return string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getCustomerAuthToken(CustomerUser $customerUser)
    {
       $braintreeId = $customerUser->getBraintreeId();

       // if the customer doesn't already exist with braintree create it
       if (!$braintreeId) {
           $result = $this->createBraintreeCustomer($customerUser);

           // If we failed to create fallback to a generic auth token with no vaulting
           if (!$result->success) {
               return  $this->getAuthToken();
           }

           $braintreeId = $customerUser->getBraintreeId();
       }

       // ensure we can find the customer with that ID otherwise fallback to generic token
        try {
           $this->braintreeGateway->customer()->find($braintreeId);
        } catch (NotFound $exception) {
            return  $this->getAuthToken();
        }

        return $this->braintreeGateway->clientToken()->generate(
            [
                'customerId' => $braintreeId
            ]
        );
    }

    /**
     * Generate a generic auth token for braintree
     * @return string
     */
    public function getAuthToken()
    {
        return $this->braintreeGateway->clientToken()->generate();
    }

    /**
     * Charge the payment nonce
     * @param array $params
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public function sale(array $params)
    {
        return $this->braintreeGateway->transaction()->sale($params);
    }

    /**
     * @param CustomerUser $customerUser
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createBraintreeCustomer(CustomerUser $customerUser)
    {
        $result = $this->braintreeGateway->customer()->create(
            [
                'firstName' => $customerUser->getFirstName(),
                'lastName'  => $customerUser->getLastName(),
                'company'    => $customerUser->getCustomer()->getName(),
                'email'      => $customerUser->getEmail()
            ]
        );

        if ($result->success) {
            $em = $this->doctrineHelper->getEntityManager(CustomerUser::class);
            $customerUser->setBraintreeId($result->customer->id);
            $em->flush($customerUser);
        }

        return $result;
    }
}