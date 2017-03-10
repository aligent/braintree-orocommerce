<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Matcher;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiWebsiteBundle\Matcher\CookieWebsiteMatcher;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CookieWebsiteMatcherTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var RequestStack|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestStack;

    /**
     * @var CookieWebsiteMatcher
     */
    protected $matcher;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->matcher = new CookieWebsiteMatcher(
            $this->configManager,
            $this->registry,
            $this->requestStack
        );
    }

    public function testGetLabel()
    {
        $this->assertSame('oro.multiwebsite.matcher.cookie.label', $this->matcher->getLabel());
    }

    public function testGetTooltip()
    {
        $this->assertSame('oro.multiwebsite.matcher.cookie.tooltip', $this->matcher->getTooltip());
    }

    public function testMatchNoRequest()
    {
        $this->requestStack->expects($this->once())
            ->method('getMasterRequest');

        $this->assertNull($this->matcher->match());
    }
    
    public function testMatchNoCookieConfigured()
    {
        $request = Request::create('/');
        
        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);
        
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_multiwebsite.website_cookie_name');

        $this->assertNull($this->matcher->match());
    }

    public function testMatchNoSuchCookie()
    {
        $request = Request::create('/', Request::METHOD_GET, [], ['some' => 'other']);

        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_multiwebsite.website_cookie_name')
            ->willReturn('testName');

        $this->assertNull($this->matcher->match());
    }

    public function testMatchNoCookieValue()
    {
        $request = Request::create('/', Request::METHOD_GET, [], ['testName' => null]);

        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_multiwebsite.website_cookie_name')
            ->willReturn('testName');

        $this->configManager->expects($this->never())
            ->method('getValues');

        $this->assertNull($this->matcher->match());
    }

    public function testMatchCookieValue()
    {
        $website1 = $this->getEntity(Website::class, ['id' => 1]);
        $website3 = $this->getEntity(Website::class, ['id' => 3]);
        $websites = [$website1, $website3];
        $websiteIds = [1, 3];
        $request = Request::create('/', Request::METHOD_GET, [], ['testName' => 'other']);

        /** @var WebsiteRepository|\PHPUnit_Framework_MockObject_MockObject $repo */
        $repo = $this->getMockBuilder(WebsiteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->once())
            ->method('getWebsiteIdentifiers')
            ->willReturn($websiteIds);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($repo);

        $em->expects($this->any())
            ->method('getReference')
            ->withConsecutive(
                [Website::class, 1],
                [Website::class, 3]
            )
            ->willReturnMap(
                [
                    [Website::class, 1, $website1],
                    [Website::class, 3, $website3],
                ]
            );

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($em);

        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_multiwebsite.website_cookie_name')
            ->willReturn('testName');

        $this->configManager->expects($this->once())
            ->method('getValues')
            ->with('oro_multiwebsite.website_cookie_value', $websites)
            ->willReturn([1 => 'one', 3 => 'other']);

        $this->assertSame($website3, $this->matcher->match());
    }
}
