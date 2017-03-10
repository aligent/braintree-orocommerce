<?php

namespace Oro\Bundle\UserProBundle\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserProBundle\Entity\AuthenticationCode;
use Oro\Bundle\UserProBundle\Security\TwoFactorTransportInterface;

class TwoFactorMailerTransport implements TwoFactorTransportInterface
{
    /** @var Processor */
    protected $mailProcessor;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param Processor $mailProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(Processor $mailProcessor, LoggerInterface $logger)
    {
        $this->mailProcessor = $mailProcessor;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function send(UserInterface $user, AuthenticationCode $code, array $data)
    {
        if (!$user instanceof AbstractUser) {
            return false;
        }

        try {
            return (bool) $this->mailProcessor->sendAuthenticationCodeEmail(
                $user,
                $code->getCode(),
                $code->getExpiresAt(),
                $data
            );
        } catch (\Swift_SwiftException $exception) {
            $this->logger->error('Unable to send authentication code email', ['exception' => $exception]);

            return false;
        }
    }
}
