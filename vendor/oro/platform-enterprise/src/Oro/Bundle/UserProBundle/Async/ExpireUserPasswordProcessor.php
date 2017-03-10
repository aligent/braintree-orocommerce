<?php

namespace Oro\Bundle\UserProBundle\Async;

use Psr\Log\LoggerInterface;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Model\EmailTemplateInterface;
use Oro\Bundle\NotificationBundle\Model\EmailNotification;
use Oro\Bundle\NotificationBundle\Manager\EmailNotificationManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\Repository\UserRepository;

class ExpireUserPasswordProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    const TEMPLATE_NAME = 'force_reset_password';

    /** @var EmailNotificationManager */
    protected $notificationManager;

    /** @var UserManager */
    protected $userManager;

    /** @var RegistryInterface */
    protected $doctrine;

    /** @var JobRunner */
    protected $jobRunner;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param EmailNotificationManager $notificationManager
     * @param UserManager              $userManager
     * @param RegistryInterface        $doctrine
     * @param JobRunner                $jobRunner
     * @param LoggerInterface          $logger
     */
    public function __construct(
        EmailNotificationManager $notificationManager,
        UserManager $userManager,
        RegistryInterface $doctrine,
        JobRunner $jobRunner,
        LoggerInterface $logger
    ) {
        $this->notificationManager = $notificationManager;
        $this->userManager = $userManager;
        $this->doctrine = $doctrine;
        $this->jobRunner = $jobRunner;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());

        if (!isset($body['userId'])) {
            $this->logger->critical(
                sprintf(
                    '[ExpireUserPasswordProcessor] Got invalid message: "%s"',
                    $message->getBody()
                ),
                ['message' => $message]
            );

            return self::REJECT;
        }

        $template = $this->getEmailTemplate();
        if (!$template) {
            $this->logger->critical(
                sprintf('[ExpireUserPasswordProcessor] Cannot find email template "%s"', self::TEMPLATE_NAME),
                ['template' => self::TEMPLATE_NAME]
            );

            return self::REJECT;
        }

        $user = $this->getUserRepository()->findOneBy(['id' => $body['userId'], 'enabled' => true]);
        if (!$user instanceof User) {
            $this->logger->error(
                sprintf('[ExpireUserPasswordProcessor] User not found. id: %s', $body['userId']),
                ['message' => $message]
            );

            return self::REJECT;
        }

        $result = $this->jobRunner->runUnique(
            $message->getMessageId(),
            sprintf('%s:%s', 'oro.userpro.expire_user_password', $body['userId']),
            function () use ($user, $template) {
                $userEmail = $user->getEmail();

                $user->setConfirmationToken($user->generateToken());

                $this->userManager->setAuthStatus($user, UserManager::STATUS_EXPIRED);
                $this->userManager->updateUser($user);

                $passResetNotification = new EmailNotification($template, [$userEmail]);
                $this->notificationManager->process($user, [$passResetNotification]);

                $this->getUserEntityManager()->flush();

                return true;
            }
        );

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * @return ObjectManager
     */
    protected function getUserEntityManager()
    {
        return $this->doctrine->getManagerForClass(User::class);
    }

    /**
     * @return UserRepository
     */
    protected function getUserRepository()
    {
        return $this->getUserEntityManager()->getRepository(User::class);
    }

    /**
     * get Instance of the email template
     *
     * @return EmailTemplateInterface
     */
    protected function getEmailTemplate()
    {
        return $this->doctrine->getManagerForClass(EmailTemplate::class)
            ->getRepository(EmailTemplate::class)
            ->findOneBy(['name' => self::TEMPLATE_NAME]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::EXPIRE_USER_PASSWORD];
    }
}
