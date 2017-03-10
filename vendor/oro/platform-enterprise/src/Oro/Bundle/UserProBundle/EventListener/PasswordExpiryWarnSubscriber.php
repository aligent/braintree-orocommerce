<?php

namespace Oro\Bundle\UserProBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserProBundle\Provider\PasswordChangePeriodConfigProvider;

class PasswordExpiryWarnSubscriber implements EventSubscriberInterface
{
    /** @var PasswordChangePeriodConfigProvider */
    protected $configProvider;

    /** @var Session */
    protected $session;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param PasswordChangePeriodConfigProvider $configProvider
     * @param Session $session
     * @param TranslatorInterface $translator
     */
    public function __construct(
        PasswordChangePeriodConfigProvider $configProvider,
        Session $session,
        TranslatorInterface $translator
    ) {
        $this->configProvider = $configProvider;
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'];
    }

    /**
     * @param  InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        if (null === $passwordExpiryDate = $user->getPasswordExpiresAt()) {
            return;
        }

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        if ($passwordExpiryDate < $now) {
            return;
        }

        $days = $now->diff($passwordExpiryDate)->days;
        $validityDays = $this->configProvider->getExpiryPeriod();
        $maxNotificationDay = max($this->configProvider->getNotificationDays());

        // check if notifications should start appearing
        if ($days > min($validityDays, $maxNotificationDay)) {
            return;
        }

        $message = $this->translator
            ->transChoice('oro.userpro.password.expiration.message', $days, ['%count%' => $days]);

        $this->session->getFlashBag()->add('warning', $message);
    }
}
