<?php

namespace Oro\Bundle\UserProBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserProBundle\Event\TwoFactorAuthenticationEvent;
use Oro\Bundle\UserProBundle\Event\TwoFactorAuthenticationEvents;
use Oro\Bundle\UserProBundle\Manager\TwoFactorCodeManager;
use Oro\Bundle\UserProBundle\Sender\TwoFactorCodeSender;

class TwoFactorAuthenticationSubscriber implements EventSubscriberInterface
{
    /** @var TwoFactorCodeManager */
    protected $codeManager;

    /** @var TwoFactorCodeSender */
    protected $codeSender;

    /**
     * @param TwoFactorCodeManager $codeManager
     * @param TwoFactorCodeSender  $codeSender
     */
    public function __construct(TwoFactorCodeManager $codeManager, TwoFactorCodeSender $codeSender)
    {
        $this->codeManager = $codeManager;
        $this->codeSender = $codeSender;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TwoFactorAuthenticationEvents::START => 'onAuthenticationStart',
        ];
    }

    /**
     * @param TwoFactorAuthenticationEvent $event
     */
    public function onAuthenticationStart(TwoFactorAuthenticationEvent $event)
    {
        $user = $event->getToken()->getUser();

        if ($user instanceof AbstractUser) {
            $code = $this->codeManager->create($user);
            $this->codeSender->send($user, $code);
        }
    }
}
