<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Matcher;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherInterface;
use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherRegistry;

class WebsiteMatcherRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var WebsiteMatcherRegistry
     */
    protected $matcherRegistry;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->matcherRegistry = new WebsiteMatcherRegistry($this->configManager);
    }

    public function testGetRegisteredMatchers()
    {
        /** @var WebsiteMatcherInterface $matcher */
        $matcher = $this->createMock(WebsiteMatcherInterface::class);
        $this->matcherRegistry->addMatcher('matcher', $matcher);

        $this->assertEquals(['matcher' => $matcher], $this->matcherRegistry->getRegisteredMatchers());
    }

    public function testGetEnabledMatchersNoConfig()
    {
        /** @var WebsiteMatcherInterface $matcher1 */
        $matcher1 = $this->createMock(WebsiteMatcherInterface::class);
        /** @var WebsiteMatcherInterface $matcher1 */
        $matcher2 = $this->createMock(WebsiteMatcherInterface::class);
        /** @var WebsiteMatcherInterface $matcher1 */
        $matcher3 = $this->createMock(WebsiteMatcherInterface::class);
        $this->matcherRegistry->addMatcher('matcher1', $matcher1);
        $this->matcherRegistry->addMatcher('matcher2', $matcher1);
        $this->matcherRegistry->addMatcher('matcher3', $matcher1);

        $expected = [
            'matcher1' => $matcher1,
            'matcher2' => $matcher2,
            'matcher3' => $matcher3,
        ];
        $this->assertEquals($expected, $this->matcherRegistry->getEnabledMatchers());
    }

    public function testGetEnabledMatchers()
    {
        /** @var WebsiteMatcherInterface $matcher1 */
        $matcher1 = $this->createMock(WebsiteMatcherInterface::class);
        /** @var WebsiteMatcherInterface $matcher1 */
        $matcher2 = $this->createMock(WebsiteMatcherInterface::class);
        /** @var WebsiteMatcherInterface $matcher1 */
        $matcher3 = $this->createMock(WebsiteMatcherInterface::class);
        $this->matcherRegistry->addMatcher('matcher1', $matcher1);
        $this->matcherRegistry->addMatcher('matcher2', $matcher1);
        $this->matcherRegistry->addMatcher('matcher3', $matcher1);

        $matchersConfig = [
            [
                'enabled' => true,
                'matcher_alias' => 'matcher3',
                'priority' => 3
            ],
            [
                'enabled' => true,
                'matcher_alias' => 'matcher2',
                'priority' => 2
            ],
            [
                'enabled' => false,
                'matcher_alias' => 'matcher1',
                'priority' => 1
            ],
        ];
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_multiwebsite.website_matchers_settings')
            ->willReturn($matchersConfig);

        $expected = [
            'matcher3' => $matcher3,
            'matcher2' => $matcher2,
        ];
        $actual = $this->matcherRegistry->getEnabledMatchers();
        $this->assertEquals(array_keys($expected), array_keys($actual), 'Matcher are not sorted correctly');
        $this->assertEquals($expected, $actual, 'Matchers list do not match');
    }
}
