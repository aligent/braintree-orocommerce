<?php

namespace Oro\Bundle\UserProBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User authentication code
 *
 * @ORM\Table(name="oro_pro_user_auth_code",
 *      indexes={
 *          @ORM\Index(name="user_auth_code_idx",columns={"code"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Oro\Bundle\UserProBundle\Entity\Repository\AuthenticationCodeRepository")
 */
class AuthenticationCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $user;

    /**
     * Generated code.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $code;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires_at", type="datetime")
     */
    protected $expiresAt;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTime $expiresAt
     */
    public function setExpiresAt(\DateTime $expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    public function isExpired()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        return $now > $this->getExpiresAt();
    }
}
