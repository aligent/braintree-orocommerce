<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\UserBundle\Tests\Unit\Stub\UserStub;

class User extends UserStub
{
    /** @var int */
    protected $failedLoginCount;

    /** @var \DateTime */
    protected $password_expires_at;

    /**
     * @param int $failedLoginCount
     *
     * @return $this
     */
    public function setFailedLoginCount($failedLoginCount)
    {
        $this->failedLoginCount = $failedLoginCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getFailedLoginCount()
    {
        return $this->failedLoginCount;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setPasswordExpiresAt(\DateTime $date)
    {
        $this->password_expires_at = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPasswordExpiresAt()
    {
        return $this->password_expires_at;
    }
}
