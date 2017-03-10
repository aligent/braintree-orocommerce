<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MultiWebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherInterface;
use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherRegistry;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WebsiteManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    /**
     * @var FrontendHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $frontendHelper;

    protected function setUp()
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->frontendHelper = $this->getMockBuilder(FrontendHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->websiteManager = new WebsiteManager($this->managerRegistry, $this->frontendHelper);
        $this->websiteManager->setContainer($this->container);
    }
    
    public function testGetCurrentWebsiteNoMatchers()
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $website = $this->createMock(Website::class);

        /** @var WebsiteMatcherRegistry|\PHPUnit_Framework_MockObject_MockObject $matcherRegistry */
        $matcherRegistry = $this->getMockBuilder(WebsiteMatcherRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $matcherRegistry->expects($this->once())
            ->method('getEnabledMatchers')
            ->willReturn([]);
        
        $this->container->expects($this->once())
            ->method('get')
            ->with('oro_multiwebsite.matcher.website_matcher_registry')
            ->willReturn($matcherRegistry);
        
        $repo = $this->getMockBuilder(WebsiteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn($website);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repo);
        
        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($em);

        $this->assertSame($website, $this->websiteManager->getCurrentWebsite());
    }

    public function testGetCurrentWebsiteFromMatcher()
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $website = $this->createMock(Website::class);

        $matcher1 = $this->createMock(WebsiteMatcherInterface::class);
        $matcher1->expects($this->once())
            ->method('match')
            ->willReturn($website);
        $matcher2 = $this->createMock(WebsiteMatcherInterface::class);
        $matcher2->expects($this->never())
            ->method('match');

        /** @var WebsiteMatcherRegistry|\PHPUnit_Framework_MockObject_MockObject $matcherRegistry */
        $matcherRegistry = $this->getMockBuilder(WebsiteMatcherRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $matcherRegistry->expects($this->once())
            ->method('getEnabledMatchers')
            ->willReturn([$matcher1, $matcher2]);

        $this->container->expects($this->once())
            ->method('get')
            ->with('oro_multiwebsite.matcher.website_matcher_registry')
            ->willReturn($matcherRegistry);

        $this->managerRegistry->expects($this->never())
            ->method('getManagerForClass');

        $this->assertSame($website, $this->websiteManager->getCurrentWebsite());
    }

    public function testGetCurrentWebsiteNonFrontend()
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->container->expects($this->never())
            ->method('get');

        $this->assertNull($this->websiteManager->getCurrentWebsite());
    }
}
