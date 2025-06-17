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
use Braintree\Exception\NotFound;
use Braintree\Gateway as BraintreeGateway;
use Braintree\Result\Error as BraintreeErrorResult;
use Braintree\Result\Successful as BraintreeSuccessResult;
use Doctrine\ORM\ORMException;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class Gateway
{
    const PRODUCTION = 'production';
    const SANDBOX = 'sandbox';

    protected BraintreeConfigInterface $config;
    protected BraintreeGateway $braintreeGateway;
    protected DoctrineHelper $doctrineHelper;

    public function __construct(BraintreeConfigInterface $config, DoctrineHelper $doctrineHelper)
    {
        $this->config = $config;
        $this->doctrineHelper = $doctrineHelper;
        $this->braintreeGateway = new BraintreeGateway(
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
     */
    public function getCustomerAuthToken(CustomerUser $customerUser): string
    {
        if (!method_exists($customerUser, 'getBraintreeId')) {
            // Schema Migrations most likely have not been run
            return '';
        }

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
            return $this->getAuthToken();
        }

        /** @phpstan-ignore-next-line BrainTree ClientTokenGateway is incorrectly type-hinted, it accepts an array */
        return $this->braintreeGateway->clientToken()->generate([
            'customerId' => $braintreeId,
        ]);
    }

    /**
     * Generate a generic auth token for braintree
     * @return string
     */
    public function getAuthToken(): string
    {
        return $this->braintreeGateway->clientToken()->generate([
            'merchantAccountId' => $this->config->getMerchantAccountId()
        ]);
    }

    /**
     * Charge the payment nonce
     * @param array<string,mixed> $params
     * @return BraintreeErrorResult|BraintreeSuccessResult
     */
    public function sale(array $params): BraintreeErrorResult|BraintreeSuccessResult
    {
        return $this->braintreeGateway->transaction()->sale($params);
    }

    /**
     * @param CustomerUser $customerUser
     * @return BraintreeErrorResult|BraintreeSuccessResult
     */
    public function createBraintreeCustomer(CustomerUser $customerUser): BraintreeErrorResult|BraintreeSuccessResult
    {
        if (!method_exists($customerUser, 'setBraintreeId')) {
            return new BraintreeErrorResult('Braintree Bundle not installed correctly');
        }

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
            /* @phpstan-ignore-next-line The Braintree Result classes unfortunately use magic methods */
            $customerUser->setBraintreeId($result->customer->id);

            try {
                $em->flush($customerUser);
            } catch (ORMException $e) {
                return new BraintreeErrorResult($e->getMessage());
            }
        }

        return $result;
    }
}
