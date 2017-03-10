<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\UserProBundle\Provider\PasswordChangePeriodConfigProvider;
use Oro\Bundle\UserProBundle\EventListener\PasswordExpiryPeriodChangeListener;

class PasswordExpiryPeriodChangeListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Registry */
    protected $registry;

    /** @var  PasswordChangePeriodConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $provider;

    /** @var \DateTime */
    protected $expiryDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->expiryDate = new \DateTime('+3 Days');

        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = $this->getMockBuilder(PasswordChangePeriodConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test update value on enabled setting.
     */
    public function testUpdatedValue()
    {
        $this->mockRegistryMethods();

        $this->provider->expects($this->once())
            ->method('isPasswordChangePeriodEnabled')
            ->willReturn(true);
        $this->provider->expects($this->once())
            ->method('getPasswordExpiryDateFromNow')
            ->willReturn($this->expiryDate);

        $event = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->exactly(2))
            ->method('isChanged')
            ->will($this->onConsecutiveCalls(false, true));

        $listener = new PasswordExpiryPeriodChangeListener($this->registry, $this->provider);
        $listener->onConfigUpdate($event);
    }

    /**
     * Test Enable/Disable setting.
     */
    public function testEnableSetting()
    {
        $this->mockRegistryMethods();

        $this->provider->expects($this->once())
            ->method('getPasswordExpiryDateFromNow')
            ->willReturn($this->expiryDate);

        $event = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('isChanged')
            ->willReturn(true);

        $listener = new PasswordExpiryPeriodChangeListener($this->registry, $this->provider);
        $listener->onConfigUpdate($event);
    }

    /**
     * Test changing value on disabled setting.
     */
    public function testChangeValueWhenSettingDisabled()
    {
        $this->provider->expects($this->once())
            ->method('isPasswordChangePeriodEnabled')
            ->willReturn(false);

        $event = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('isChanged')
            ->willReturn(false);

        $listener = new PasswordExpiryPeriodChangeListener($this->registry, $this->provider);
        $listener->onConfigUpdate($event);
    }

    /**
     * Add DB query stubs/
     */
    private function mockRegistryMethods()
    {
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $repo = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\Repository\UserRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())->method('execute')->willReturnSelf();
        $qb->expects($this->once())->method('set')->willReturnSelf();
        $qb->expects($this->once())->method('setParameter')->willReturnSelf();
        $qb->expects($this->once())->method('getQuery')->willReturn($query);
        $qb->expects($this->once())->method('update')->willReturnSelf();
        $repo->expects($this->once())->method('createQueryBuilder')->willReturn($qb);
        $this->registry->expects($this->atLeastOnce())->method('getRepository')->willReturn($repo);
    }
}
