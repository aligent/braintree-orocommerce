<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Async;

use Psr\Log\LoggerInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\Null\NullSession;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\UserProBundle\Async\ExpireUserPasswordProcessor;
use Oro\Bundle\NotificationBundle\Manager\EmailNotificationManager;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;

class ExpireUserPasswordProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ExpireUserPasswordProcessor */
    protected $processor;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ObjectRepository */
    protected $userRepo;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EmailNotificationManager */
    protected $notificationManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|JobRunner */
    protected $jobRunner;

    protected function setUp()
    {
        $userManager = $this->getMockForClass(UserManager::class);
        $doctrine = $this->getMockForClass(RegistryInterface::class);
        $em = $this->getMockForClass(ObjectManager::class);
        $logger = $this->getMockForClass(LoggerInterface::class);
        $emailTemplateRepo = $this->getMockForClass(ObjectRepository::class);
        $emailTemplate = $this->getMockForClass(EmailTemplate::class);

        $emailTemplateRepo->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn($emailTemplate);

        $this->userRepo = $this->getMockForClass(ObjectRepository::class);

        $em->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturnMap(
                [
                    [EmailTemplate::class, $emailTemplateRepo],
                    [User::class, $this->userRepo],
                ]
            );
        $em->expects(self::once())->method('flush');

        $doctrine->expects(self::atLeastOnce())
            ->method('getManagerForClass')
            ->willReturn($em);

        $this->notificationManager = $this->getMockForClass(EmailNotificationManager::class);

        $this->jobRunner = $this->createJobRunnerMock();

        $this->processor = new ExpireUserPasswordProcessor(
            $this->notificationManager,
            $userManager,
            $doctrine,
            $this->jobRunner,
            $logger
        );
    }

    public function testProcess()
    {
        $this->jobRunner
            ->expects($this->once())
            ->method('runUnique')
            ->with('message-id', 'oro.userpro.expire_user_password:1')
            ->will($this->returnCallback(function ($ownerId, $name, $callback) {
                $callback($this->jobRunner);

                return true;
            }))
        ;

        $data = ['userId' => '1'];

        $session = new NullSession();
        $message = $session->createMessage(json_encode($data));
        $message->setMessageId('message-id');
        $this->userRepo->expects(self::once())
            ->method('findOneBy')
            ->willReturnCallback(
                function () use ($data) {
                    $user = new User();
                    $user->setId($data['userId']);

                    return $user;
                }
            );
        $this->notificationManager->expects(self::exactly(1))->method('process');

        $result = $this->processor->process($message, $session);
        $this->assertEquals(MessageProcessorInterface::ACK, $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JobRunner
     */
    private function createJobRunnerMock()
    {
        return $this->createMock(JobRunner::class);
    }

    /**
     * Get Mock for class with disabled constructor
     *
     * @param $class
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|$class
     */
    protected function getMockForClass($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
