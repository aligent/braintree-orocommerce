<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Matcher;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiWebsiteBundle\Matcher\PathWebsiteMatcher;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PathWebsiteMatcherTest extends \PHPUnit_Framework_TestCase
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
     * @var PathWebsiteMatcher
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

        $this->matcher = new PathWebsiteMatcher(
            $this->configManager,
            $this->registry,
            $this->requestStack
        );
    }

    public function testGetLabel()
    {
        $this->assertSame('oro.multiwebsite.matcher.path.label', $this->matcher->getLabel());
    }

    public function testMatchNoRequest()
    {
        $this->requestStack->method('getMasterRequest')->willReturn(null);

        $this->assertNull($this->matcher->match());
    }

    /**
     * @dataProvider matchDataProvider
     *
     * @param array $websiteUrls
     * @param array $websiteSecureUrls
     * @param string $requestUrl
     * @param string|null $matchedUrl
     * @param Website|null $expected
     */
    public function testMatch(
        array $websiteUrls,
        array $websiteSecureUrls,
        $requestUrl,
        $matchedUrl,
        Website $expected = null
    ) {
        $websiteIds = [1, 2, 3];
        $website1 = $this->getEntity(Website::class, ['id' => 1]);
        $website2 = $this->getEntity(Website::class, ['id' => 2]);
        $website3 = $this->getEntity(Website::class, ['id' => 3]);
        $websites = [$website1, $website2, $website3];

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->method('getUri')->willReturn($requestUrl);
        $this->requestStack->method('getMasterRequest')->willReturn($request);

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
                [Website::class, 2],
                [Website::class, 3]
            )
            ->willReturnMap(
                [
                    [Website::class, 1, $website1],
                    [Website::class, 2, $website2],
                    [Website::class, 3, $website3],
                ]
            );

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($em);

        $this->configManager->expects($this->any())
            ->method('getValues')
            ->withConsecutive(
                ['oro_website.url', $websites],
                ['oro_website.secure_url', $websites]
            )
            ->willReturnMap(
                [
                    ['oro_website.url', $websites, false, false, $websiteUrls],
                    ['oro_website.secure_url', $websites, false, false, $websiteSecureUrls],
                ]
            );

        $this->assertEquals($expected, $this->matcher->match());
        $this->assertEquals($matchedUrl, $this->matcher->getMatchedUrl());
    }

    public function matchDataProvider()
    {
        return [
            'no matching sites' => [
                'websiteUrls' => [
                    1 => 'https://orocommerce.com/',
                    2 => 'https://orocommerce.com/en/',
                    3 => 'https://orocommerce.com/eu/',
                ],
                'websiteSecureUrls' => [
                    1 => 'https://orocommerce.com/sec/',
                    2 => 'https://orocommerce.com/en/sec/',
                    3 => 'https://orocommerce.com/eu/sec/',
                ],
                'requestUrl' => 'https://commerce.com/product/?test=1',
                'matchedUrl' => null,
                'expected' => null,
            ],
            'sub_folder match by url' => [
                'websiteUrls' => [
                    1 => 'https://orocommerce.com/',
                    2 => 'https://orocommerce.com/en/',
                    3 => 'https://orocommerce.com/eu/',
                ],
                'websiteSecureUrls' => [
                    1 => 'https://orocommerce.com/sec/',
                    2 => 'https://orocommerce.com/en/sec/',
                    3 => 'https://orocommerce.com/eu/sec/',
                ],
                'requestUrl' => 'https://orocommerce.com/product/?test=1',
                'matchedUrl' => 'https://orocommerce.com/',
                'expected' => $this->getEntity(Website::class, ['id' => 1]),
            ],
            'sub_folder match by url longest match' => [
                'websiteUrls' => [
                    1 => 'https://orocommerce.com/',
                    2 => 'https://orocommerce.com/en/',
                    3 => 'https://orocommerce.com/eu/',
                ],
                'websiteSecureUrls' => [
                    1 => 'https://orocommerce.com/sec/',
                    2 => 'https://orocommerce.com/en/sec/',
                    3 => 'https://orocommerce.com/eu/sec/',
                ],
                'requestUrl' => 'https://orocommerce.com/en/product/?test=1',
                'matchedUrl' => 'https://orocommerce.com/en/',
                'expected' => $this->getEntity(Website::class, ['id' => 2]),
            ],
            'sub_folder match by secure url' => [
                'websiteUrls' => [
                    1 => 'https://orocommerce.com/',
                    2 => 'https://orocommerce.com/en/',
                    3 => 'https://orocommerce.com/eu/',
                ],
                'websiteSecureUrls' => [
                    1 => 'https://orocommerce.com/sec/',
                    2 => 'https://orocommerce.com/en/sec/',
                    3 => 'https://orocommerce.com/eu/sec/',
                ],
                'requestUrl' => 'https://orocommerce.com/eu/sec/product/?test=1',
                'matchedUrl' => 'https://orocommerce.com/eu/sec/',
                'expected' => $this->getEntity(Website::class, ['id' => 3]),
            ],
            'sub_domain match' => [
                'websiteUrls' => [
                    1 => 'https://orocommerce.com/',
                    2 => 'https://en.orocommerce.com/',
                    3 => 'https://eu.orocommerce.com/',
                ],
                'websiteSecureUrls' => [
                    1 => 'https://orocommerce.com/sec/',
                    2 => 'https://en.orocommerce.com/sec/',
                    3 => 'https://eu.orocommerce.com/sec/',
                ],
                'requestUrl' => 'https://eu.orocommerce.com/product/?test=1',
                'matchedUrl' => 'https://eu.orocommerce.com/',
                'expected' => $this->getEntity(Website::class, ['id' => 3]),
            ],
        ];
    }

    public function testOnClear()
    {
        $website = new Website();

        $propertyReflection = new \ReflectionProperty(get_class($this->matcher), 'matchedWebsite');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->matcher, $website);

        $this->assertAttributeNotEmpty('matchedWebsite', $this->matcher);
        $this->matcher->onClear();
        $this->assertAttributeEmpty('matchedWebsite', $this->matcher);
    }
}
