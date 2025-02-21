<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Braintree;

use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Braintree\Customer;
use Braintree\Exception\NotFound;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class Gateway
{
    const PRODUCTION = 'production';
    const SANDBOX = 'sandbox';

    public BraintreeConfigInterface $config;

    protected \Braintree\Gateway $braintreeGateway;

    protected DoctrineHelper $doctrineHelper;

    /**
     * Gateway constructor.
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
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getCustomerAuthToken(CustomerUser $customerUser): string
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
     */
    public function getAuthToken(): string
    {
        return $this->braintreeGateway->clientToken()->generate();
    }

    /**
     * Charge the payment nonce
     */
    public function sale(array $params): Error|Successful
    {
        return $this->braintreeGateway->transaction()->sale($params);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createBraintreeCustomer(CustomerUser $customerUser): Error|Successful
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
