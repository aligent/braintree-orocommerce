<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MultiWebsiteBundle\Matcher\PathWebsiteMatcher;
use Oro\Bundle\MultiWebsiteBundle\EventListener\RoutingListener;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RoutingListenerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var RoutingListener
     */
    private $listener;

    /**
     * @var WebsiteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteManager;

    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configManager;

    /**
     * @var WebsiteUrlResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlResolver;

    /**
     * @var FrontendHelper
     */
    private $frontendHelper;

    /**
     * @var PathWebsiteMatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pathWebsiteMatcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->websiteManager = $this->getMockBuilder(WebsiteManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlResolver = $this->getMockBuilder(WebsiteUrlResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->frontendHelper = $this->getMockBuilder(FrontendHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->frontendHelper->method('isFrontendRequest')->willReturn(true);

        $this->pathWebsiteMatcher = $this->getMockBuilder(PathWebsiteMatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new RoutingListener(
            $this->configManager,
            $this->websiteManager,
            $this->urlResolver,
            $this->frontendHelper,
            $this->pathWebsiteMatcher
        );
    }

    public function testRedirectDisabled()
    {
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);
        $request = Request::create('https://orocommerce.com/product');
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('isMasterRequest')->willReturn(true);

        $this->websiteManager->method('getCurrentWebsite')->willReturn($website);
        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with('oro_multiwebsite.enable_redirect')
            ->willReturn(false);

        // assert redirect response not set
        $event->expects($this->never())
            ->method('setResponse');
        $this->listener->onRequest($event);
    }

    public function testProperUrlWithoutRedirect()
    {
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);
        $request = Request::create('https://orocommerce.com/product');

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('isMasterRequest')->willReturn(true);

        $this->websiteManager->method('getCurrentWebsite')->willReturn($website);
        $this->configManager
            ->expects($this->at(0))
            ->method('get')
            ->with('oro_multiwebsite.enable_redirect')
            ->willReturn(true);

        $this->pathWebsiteMatcher->expects($this->once())
            ->method('match')
            ->willReturn($website);

        // assert redirect response not set
        $event->expects($this->never())
            ->method('setResponse');
        $this->listener->onRequest($event);
    }

    public function testNoRedirectWhenSubrequest()
    {
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('isMasterRequest')->willReturn(false);

        // assert redirect response not set
        $event->expects($this->never())
            ->method('setResponse');
        $this->listener->onRequest($event);
    }

    public function testRedirectToProperSecureUrl()
    {
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);
        /** @var Website $websiteByPath */
        $websiteByPath = $this->getEntity(Website::class, ['id' => 2]);

        $request = Request::create('https://eu.orocommerce.com/product?test=1&test2=2');
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('isMasterRequest')->willReturn(true);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['oro_multiwebsite.enable_redirect', false, false, null, true],
                    ['oro_website.secure_url', false, false, $websiteByPath, 'https://eu.orocommerce.com/'],
                    ['oro_website.secure_url', false, false, $website, 'https://us.orocommerce.com/']
                ]
            );

        $this->pathWebsiteMatcher->expects($this->once())
            ->method('match')
            ->willReturn($websiteByPath);

        $this->pathWebsiteMatcher->expects($this->once())
            ->method('getMatchedUrl')
            ->willReturn('https://eu.orocommerce.com/');

        $this->urlResolver->expects($this->any())
            ->method('getWebsiteSecureUrl')
            ->willReturnMap(
                [
                    [$website, 'https://us.orocommerce.com/'],
                    [$websiteByPath, 'https://eu.orocommerce.com/'],
                ]
            );
        $this->urlResolver->expects($this->any())
            ->method('getWebsiteUrl')
            ->willReturnMap(
                [
                    [$website, 'http://us.orocommerce.com/'],
                    [$websiteByPath, 'http://eu.orocommerce.com/'],
                ]
            );

        $event->expects($this->once())
            ->method('setResponse')
            ->with(new RedirectResponse('https://us.orocommerce.com/product?test=1&test2=2'));
        $this->listener->onRequest($event);
    }

    public function testRedirectToProperUrl()
    {
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);
        /** @var Website $websiteByPath */
        $websiteByPath = $this->getEntity(Website::class, ['id' => 2]);

        $request = Request::create('http://eu.orocommerce.com/product?test=1&test2=2');
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('isMasterRequest')->willReturn(true);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['oro_multiwebsite.enable_redirect', false, false, null, true],
                    ['oro_website.secure_url', false, false, $websiteByPath, 'https://eu.orocommerce.com/'],
                    ['oro_website.secure_url', false, false, $website, 'https://us.orocommerce.com/']
                ]
            );

        $this->pathWebsiteMatcher->expects($this->once())
            ->method('match')
            ->willReturn($websiteByPath);

        $this->pathWebsiteMatcher->expects($this->once())
            ->method('getMatchedUrl')
            ->willReturn('http://eu.orocommerce.com/');

        $this->urlResolver->expects($this->any())
            ->method('getWebsiteSecureUrl')
            ->willReturnMap(
                [
                    [$website, 'https://us.orocommerce.com/'],
                    [$websiteByPath, 'https://eu.orocommerce.com/'],
                ]
            );
        $this->urlResolver->expects($this->any())
            ->method('getWebsiteUrl')
            ->willReturnMap(
                [
                    [$website, 'http://us.orocommerce.com/'],
                    [$websiteByPath, 'http://eu.orocommerce.com/'],
                ]
            );

        $event->expects($this->once())
            ->method('setResponse')
            ->with(new RedirectResponse('http://us.orocommerce.com/product?test=1&test2=2'));
        $this->listener->onRequest($event);
    }

    public function testWebsiteWithSameUrlFound()
    {
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);
        /** @var Website $websiteByPath */
        $websiteByPath = $this->getEntity(Website::class, ['id' => 2]);

        $request = Request::create('http://eu.orocommerce.com/product?test=1&test2=2');
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('isMasterRequest')->willReturn(true);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['oro_multiwebsite.enable_redirect', false, false, null, true],
                    ['oro_website.secure_url', false, false, $websiteByPath, 'https://eu.orocommerce.com/'],
                    ['oro_website.secure_url', false, false, $website, 'https://eu.orocommerce.com/']
                ]
            );

        $this->pathWebsiteMatcher->expects($this->once())
            ->method('match')
            ->willReturn($websiteByPath);

        $this->pathWebsiteMatcher->expects($this->never())
            ->method('getMatchedUrl');

        $this->urlResolver->expects($this->any())
            ->method('getWebsiteSecureUrl')
            ->willReturnMap(
                [
                    [$website, 'https://eu.orocommerce.com/'],
                    [$websiteByPath, 'https://eu.orocommerce.com/'],
                ]
            );
        $this->urlResolver->expects($this->any())
            ->method('getWebsiteUrl')
            ->willReturnMap(
                [
                    [$website, 'http://eu.orocommerce.com/'],
                    [$websiteByPath, 'http://eu.orocommerce.com/'],
                ]
            );

        // assert redirect response not set
        $event->expects($this->never())
            ->method('setResponse');
        $this->listener->onRequest($event);
    }

    public function testWebsiteWithEmptyWebsiteUrl()
    {
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);
        $websiteByPath = null;

        $request = Request::create('http://eu.orocommerce.com/product?test=1&test2=2');
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('isMasterRequest')->willReturn(true);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['oro_multiwebsite.enable_redirect', false, false, null, true],
                    ['oro_website.secure_url', false, false, $websiteByPath, 'https://eu.orocommerce.com/'],
                    ['oro_website.secure_url', false, false, $website, 'https://eu.orocommerce.com/']
                ]
            );

        $this->pathWebsiteMatcher->expects($this->once())
            ->method('match')
            ->willReturn($websiteByPath);

        $this->pathWebsiteMatcher->expects($this->never())
            ->method('getMatchedUrl');

        $this->urlResolver->expects($this->any())
            ->method('getWebsiteSecureUrl')
            ->willReturnMap(
                [
                    [$website, 'https://eu.orocommerce.com/'],
                    [$websiteByPath, 'https://eu.orocommerce.com/'],
                ]
            );
        $this->urlResolver->expects($this->any())
            ->method('getWebsiteUrl')
            ->willReturn(null);

        // assert redirect response not set
        $event->expects($this->never())
            ->method('setResponse');
        $this->listener->onRequest($event);
    }

    public function testUrlNotConnectedToWebsiteRequested()
    {
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);

        $request = Request::create('http://orocommerce.com/product?test=1&test2=2');
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('isMasterRequest')->willReturn(true);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['oro_multiwebsite.enable_redirect', false, false, null, true],
                    ['oro_website.secure_url', false, false, $website, 'https://eu.orocommerce.com/']
                ]
            );

        $this->pathWebsiteMatcher->expects($this->once())
            ->method('match')
            ->willReturn(null);

        $this->pathWebsiteMatcher->expects($this->never())
            ->method('getMatchedUrl');

        $this->urlResolver->expects($this->any())
            ->method('getWebsiteSecureUrl')
            ->willReturnMap(
                [
                    [$website, 'https://eu.orocommerce.com/'],
                ]
            );
        $this->urlResolver->expects($this->any())
            ->method('getWebsiteUrl')
            ->willReturnMap(
                [
                    [$website, 'http://eu.orocommerce.com/'],
                ]
            );

        $event->expects($this->once())
            ->method('setResponse')
            ->with(new RedirectResponse('http://eu.orocommerce.com'));
        $this->listener->onRequest($event);
    }
}
