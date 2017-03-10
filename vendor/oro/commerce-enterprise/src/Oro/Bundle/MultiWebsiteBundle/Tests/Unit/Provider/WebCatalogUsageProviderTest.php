<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiWebsiteBundle\Provider\WebCatalogUsageProvider;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

class WebCatalogUsageProviderTest extends \PHPUnit_Framework_TestCase
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
     * @var WebCatalogUsageProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->provider = new WebCatalogUsageProvider($this->configManager, $this->registry);
    }

    public function testIsInUseGlobal()
    {
        $webCatalog = $this->getEntity(WebCatalog::class, ['id' => 1]);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(WebCatalogUsageProvider::SETTINGS_KEY)
            ->willReturn(1);
        $this->registry->expects($this->never())
            ->method($this->anything());

        $this->assertTrue($this->provider->isInUse($webCatalog));
    }

    public function testIsInUseWebsite()
    {
        $website1 = $this->getEntity(Website::class, ['id' => 1]);
        $website3 = $this->getEntity(Website::class, ['id' => 3]);
        $websiteIds = [1, 3];
        $websites = [$website1, $website3];
        $webCatalog = $this->getEntity(WebCatalog::class, ['id' => 42]);

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

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(WebCatalogUsageProvider::SETTINGS_KEY)
            ->willReturn(2);
        $this->configManager->expects($this->any())
            ->method('getValues')
            ->with('oro_web_catalog.web_catalog', $websites)
            ->willReturn(
                [
                    1 => '42',
                    3 => 43
                ]
            );

        $this->assertTrue($this->provider->isInUse($webCatalog));
    }

    public function testNotInUse()
    {
        $website1 = $this->getEntity(Website::class, ['id' => 1]);
        $website3 = $this->getEntity(Website::class, ['id' => 3]);
        $websiteIds = [1, 3];
        $websites = [$website1, $website3];
        $webCatalog = $this->getEntity(WebCatalog::class, ['id' => 42]);

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

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(WebCatalogUsageProvider::SETTINGS_KEY)
            ->willReturn(2);
        $this->configManager->expects($this->any())
            ->method('getValues')
            ->with('oro_web_catalog.web_catalog', $websites)
            ->willReturn(
                [
                    1 => '43',
                    3 => 44
                ]
            );

        $this->assertFalse($this->provider->isInUse($webCatalog));
    }
}
