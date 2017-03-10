<?php

namespace Oro\Bundle\UserProBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\BadCredentialsException as BaseBadCredentialsException;

class BadCredentialsException extends BaseBadCredentialsException
{
    /**
     * @var int
     */
    protected $remainingAttempts;

    /**
     * @param int $remainingAttempts
     */
    public function setRemainingAttempts($remainingAttempts)
    {
        $this->remainingAttempts = $remainingAttempts;
    }

    /**
     * @return int
     */
    public function getRemainingAttempts()
    {
        return $this->remainingAttempts;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return $this->remainingAttempts > 0
            ? 'oro.userpro.invalid_credentials_with_remaining'
            : 'oro.userpro.invalid_credentials_with_no_remaining';
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageData()
    {
        return [
            '{{ remainingAttempts }}' => $this->remainingAttempts,
        ];
    }

    public function serialize()
    {
        return serialize([
            $this->getToken(),
            $this->code,
            $this->message,
            $this->file,
            $this->line,
            $this->remainingAttempts,
        ]);
    }

    public function unserialize($str)
    {
        $token = null;
        list(
            $token,
            $this->code,
            $this->message,
            $this->file,
            $this->line,
            $this->remainingAttempts
            ) = unserialize($str);

        if ($token) {
            $this->setToken($token);
        }
    }
}
