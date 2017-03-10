<?php

namespace Oro\Bundle\EwsBundle\Tests\Unit\Sync;

use Oro\Bundle\EwsBundle\Sync\EwsEmailSynchronizationProcessorFactory;

class EwsEmailSynchronizationProcessorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $emailEntityBuilder = $this->getMockBuilder('Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $emailManager = $this->getMockBuilder('Oro\Bundle\EwsBundle\Manager\EwsEmailManager')
            ->disableOriginalConstructor()
            ->getMock();
        $knownEmailAddressChecker = $this->createMock('Oro\Bundle\EmailBundle\Sync\KnownEmailAddressCheckerInterface');

        $doctrine->expects($this->exactly(2))
            ->method('getManager')
            ->with(null)
            ->will($this->returnValue($em));
        $em->expects($this->once())
            ->method('isOpen')
            ->will($this->returnValue(false));
        $doctrine->expects($this->once())
            ->method('resetManager');

        $factory = new EwsEmailSynchronizationProcessorFactory($doctrine, $emailEntityBuilder);

        $result = $factory->create($emailManager, $knownEmailAddressChecker);
        $this->assertInstanceOf('Oro\Bundle\EwsBundle\Sync\EwsEmailSynchronizationProcessor', $result);
    }
}
