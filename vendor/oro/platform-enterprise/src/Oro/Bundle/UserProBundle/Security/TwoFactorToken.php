<?php

namespace Oro\Bundle\UserProBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Wrap the authenticated token and acts like Anonymous token
 * to trigger next level authentication (two factor)
 *
 * Anonymous token cannot be extended because it's not persisted into the session
 */
class TwoFactorToken extends AbstractToken
{
    /**
     * @var TokenInterface $authenticatedToken
     */
    protected $authenticatedToken;

    /**
     * @var \DateTime
     */
    protected $expiryDate;

    /**
     * @var string
     */
    protected $credentials;

    /**
     * @param TokenInterface $authenticatedToken
     * @param \DateTime $expiryDate
     * @param string $credentials
     */
    public function __construct(
        TokenInterface $authenticatedToken,
        \DateTime $expiryDate,
        $credentials = null
    ) {
        $this->authenticatedToken = $authenticatedToken;
        $this->expiryDate = $expiryDate;
        $this->credentials = $credentials;

        parent::setAuthenticated(false);
    }

    /**
     * @return TokenInterface
     */
    public function getAuthenticatedToken()
    {
        return $this->authenticatedToken;
    }

    /**
     * @return boolean
     */
    public function isExpired()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        return $this->expiryDate <= $now;
    }

    /**
     * @param string $credentials Authentication code
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return string Authentication code
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->credentials = null;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser($user)
    {
        $this->authenticatedToken->setUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->authenticatedToken->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->authenticatedToken->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->authenticatedToken, $this->expiryDate, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->authenticatedToken, $this->expiryDate, $parentString) = unserialize($serialized);

        parent::unserialize($parentString);
    }
}
