<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Matcher;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiWebsiteBundle\Matcher\EnvWebsiteMatcher;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EnvWebsiteMatcherTest extends \PHPUnit_Framework_TestCase
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
     * @var EnvWebsiteMatcher
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

        $this->matcher = new EnvWebsiteMatcher(
            $this->configManager,
            $this->registry,
            $this->requestStack
        );
    }

    public function testGetLabel()
    {
        $this->assertSame('oro.multiwebsite.matcher.env.label', $this->matcher->getLabel());
    }

    public function testGetTooltip()
    {
        $this->assertSame('oro.multiwebsite.matcher.env.tooltip', $this->matcher->getTooltip());
    }

    public function testMatchNoRequest()
    {
        $this->requestStack->expects($this->once())
            ->method('getMasterRequest');

        $this->assertNull($this->matcher->match());
    }

    public function testMatchEnvVarWithoutValue()
    {
        $website1 = $this->getEntity(Website::class, ['id' => 1]);
        $website3 = $this->getEntity(Website::class, ['id' => 3]);
        $websiteIds = [1, 3];
        $websites = [$website1, $website3];
        $request = Request::create('/', Request::METHOD_GET, [], [], [], ['IS_DEFAULT' => 1]);

        /** @var WebsiteRepository|\PHPUnit_Framework_MockObject_MockObject $repo */
        $repo = $this->getMockBuilder(WebsiteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->any())
            ->method('getWebsiteIdentifiers')
            ->willReturn($websiteIds);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->any())
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

        $this->configManager->expects($this->any())
            ->method('getValues')
            ->withConsecutive(
                ['oro_multiwebsite.matcher_env_var', $websites],
                ['oro_multiwebsite.matcher_env_value', $websites]
            )
            ->willReturnMap(
                [
                    ['oro_multiwebsite.matcher_env_var', $websites, false, false, [1 => 'VAR1', 3 => 'IS_DEFAULT']],
                    ['oro_multiwebsite.matcher_env_value', $websites, false, false, [1 => 'VAL']],
                ]
            );

        $this->assertSame($website3, $this->matcher->match());
    }

    public function testMatchEnvVarWithValue()
    {
        $website1 = $this->getEntity(Website::class, ['id' => 1]);
        $website3 = $this->getEntity(Website::class, ['id' => 3]);
        $websiteIds = [1, 3];
        $websites = [$website1, $website3];
        $request = Request::create('/', Request::METHOD_GET, [], [], [], ['IS_DEFAULT' => 'testValue']);

        /** @var WebsiteRepository|\PHPUnit_Framework_MockObject_MockObject $repo */
        $repo = $this->getMockBuilder(WebsiteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->any())
            ->method('getWebsiteIdentifiers')
            ->willReturn($websiteIds);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->any())
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

        $this->configManager->expects($this->any())
            ->method('getValues')
            ->withConsecutive(
                ['oro_multiwebsite.matcher_env_var', $websites],
                ['oro_multiwebsite.matcher_env_value', $websites]
            )
            ->willReturnMap(
                [
                    ['oro_multiwebsite.matcher_env_var', $websites, false, false, [1 => 'VAR1', 3 => 'IS_DEFAULT']],
                    ['oro_multiwebsite.matcher_env_value', $websites, false, false, [1 => 'VAL', 3 => 'testValue']],
                ]
            );

        $this->assertSame($website3, $this->matcher->match());
    }

    public function testMatchEnvVarWithValueIncorrect()
    {
        $website1 = $this->getEntity(Website::class, ['id' => 1]);
        $website3 = $this->getEntity(Website::class, ['id' => 3]);
        $websiteIds = [1, 3];
        $websites = [$website1, $website3];
        $request = Request::create('/', Request::METHOD_GET, [], [], [], ['IS_DEFAULT' => 'testValueExpected']);

        /** @var WebsiteRepository|\PHPUnit_Framework_MockObject_MockObject $repo */
        $repo = $this->getMockBuilder(WebsiteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->any())
            ->method('getWebsiteIdentifiers')
            ->willReturn($websiteIds);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->any())
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

        $this->configManager->expects($this->any())
            ->method('getValues')
            ->withConsecutive(
                ['oro_multiwebsite.matcher_env_var', $websites],
                ['oro_multiwebsite.matcher_env_value', $websites]
            )
            ->willReturnMap(
                [
                    ['oro_multiwebsite.matcher_env_var', $websites, false, false, [1 => 'VAR1', 3 => 'IS_DEFAULT']],
                    ['oro_multiwebsite.matcher_env_value', $websites, false, false, [1 => 'VAL', 3 => 'otherValue']],
                ]
            );

        $this->assertNull($this->matcher->match());
    }
}
