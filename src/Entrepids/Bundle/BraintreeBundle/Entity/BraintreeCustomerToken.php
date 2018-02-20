<?php

namespace Entrepids\Bundle\BraintreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="braintree_customer_token")
 */
class BraintreeCustomerToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $customer;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;
    
    /**
     * ORO REVIEW:
     * What is the purpose of this field?
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $transaction;
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }
    
    /**
     * Set Customer
     *
     * @param integer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        
        return $this;
    }
    
    /**
     * @return integer
     */
    public function getCustomer()
    {
        return $this->customer;
    }
    
    /**
     * Set token
     *
     * @param string $customer
     */
    public function setToken($token)
    {
        $this->token = $token;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * Set Transaction
     *
     * @param integer $transaction
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    
        return $this;
    }
    
    /**
     * @return integer
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
