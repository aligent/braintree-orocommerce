<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use Oro\Bundle\UserProBundle\Tests\Unit\Entity\Stub\User;
use Oro\Bundle\UserProBundle\EventListener\PasswordExpiryWarnSubscriber;
use Oro\Bundle\UserProBundle\Provider\PasswordChangePeriodConfigProvider;

class PasswordExpiryWarnSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var User */
    protected $user;

    public function setUp()
    {
        $this->user = new User();
    }

    /**
     * @dataProvider getDaysToPasswordExpiry
     */
    public function testPasswordExpirationMessage($daysToPasswordExpiry, $expectedFlashCount)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $expiryDate = $now->add(new \DateInterval('P' . $daysToPasswordExpiry . 'D'));
        $this->user->setPasswordExpiresAt($expiryDate);
        $subscriber = $this->getSubscriber($expectedFlashCount);
        $subscriber->onInteractiveLogin($this->getInteractiveLoginEvent($this->user));
    }

    public function getDaysToPasswordExpiry()
    {
        return [
            [1, 1], [2, 1], [3, 1], [4, 1], [5, 1], [6, 1], [7, 0], [8, 0],
        ];
    }

    /**
     * @param object $user
     *
     * @return InteractiveLoginEvent
     */
    private function getInteractiveLoginEvent($user)
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $event = $this->getMockBuilder(InteractiveLoginEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getAuthenticationToken')
            ->willReturn($token);

        return $event;
    }

    /**
     * @param int $flashCount
     *
     * @return PasswordExpiryWarnSubscriber
     */
    private function getSubscriber($flashCount = 0)
    {
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $configProvider = $this->getMockBuilder(PasswordChangePeriodConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configProvider->expects($this->once())
            ->method('getNotificationDays')
            ->willReturn([1, 3, 7]);

        $configProvider->expects($this->once())
            ->method('getExpiryPeriod')
            ->willReturn(6);

        $flashBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface')
            ->getMock();
        $flashBag->expects($this->any())
            ->method('add');

        $session->expects($this->exactly($flashCount))
            ->method('getFlashBag')
            ->willReturn($flashBag);

        return new PasswordExpiryWarnSubscriber($configProvider, $session, $translator);
    }
}
