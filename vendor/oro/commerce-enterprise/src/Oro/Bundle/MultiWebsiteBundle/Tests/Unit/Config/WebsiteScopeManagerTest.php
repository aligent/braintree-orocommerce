<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Config;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Cache\CacheProvider;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\MultiWebsiteBundle\Config\WebsiteScopeManager;

class WebsiteScopeManagerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrine;

    /**
     * @var CacheProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * @var WebsiteManager|\PHPUnit_Framework_MockObject_MockObject $websiteManager
     **/
    protected $websiteManager;

    /**
     * @var WebsiteScopeManager
     */
    protected $websiteScopeManager;

    protected function setUp()
    {
        $this->doctrine = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->cache = $this->createMock('Doctrine\Common\Cache\CacheProvider');
        $this->websiteManager = $this->getMockBuilder('Oro\Bundle\WebsiteBundle\Manager\WebsiteManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteScopeManager = new WebsiteScopeManager($this->doctrine, $this->cache);
        $this->websiteScopeManager->setWebsiteManager($this->websiteManager);
    }

    public function testGetScopedEntityName()
    {
        $this->assertEquals('website', $this->websiteScopeManager->getScopedEntityName());
    }

    public function testGetScopeId()
    {
        $this->assertEquals(0, $this->websiteScopeManager->getScopeId());
    }

    public function testGetScopeIdWithoutWebsite()
    {
        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite');
        $this->assertEquals(0, $this->websiteScopeManager->getScopeId());
    }

    public function testGetScopeIdWithWebsite()
    {
        $scopeId = 42;

        $website = $this->getEntity(Website::class, ['id' => $scopeId]);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);
        $this->assertEquals($scopeId, $this->websiteScopeManager->getScopeId());
    }

    public function testSetScopeId()
    {
        $scopeId = 2;
        $this->websiteScopeManager->setScopeId($scopeId);
        $this->websiteManager->expects($this->never())
            ->method($this->anything());
        $this->assertEquals(2, $this->websiteScopeManager->getScopeId());
    }

    public function testSetScopeIdFromEntity()
    {
        $website = $this->getEntity(Website::class, ['id' => 42]);
        $this->websiteScopeManager->setScopeIdFromEntity($website);
        $this->assertEquals(42, $this->websiteScopeManager->getScopeId());
    }
}
