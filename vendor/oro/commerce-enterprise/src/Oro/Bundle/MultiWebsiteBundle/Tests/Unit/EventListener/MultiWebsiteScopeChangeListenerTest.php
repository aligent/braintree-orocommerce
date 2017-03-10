<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\MultiWebsiteBundle\EventListener\MultiWebsiteLocalizationConfigListener;
use Oro\Bundle\MultiWebsiteBundle\EventListener\MultiWebsiteScopeChangeListener;
use Oro\Bundle\WebCatalogBundle\EventListener\WebCatalogConfigChangeListener;
use Oro\Bundle\WebsiteSearchBundle\Event\ReindexationRequestEvent;

class MultiWebsiteScopeChangeListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dispatcher;

    /**
     * @var MultiWebsiteScopeChangeListener
     */
    protected $websiteScopeChangeListener;

    protected function setUp()
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->websiteScopeChangeListener = new MultiWebsiteScopeChangeListener($this->dispatcher);
    }

    /**
     * @dataProvider onConfigurationUpdateProvider
     */
    public function testOnConfigurationUpdate($scope, $scopeId, $expectedReindexationEvent)
    {
        /** @var ConfigUpdateEvent|\PHPUnit_Framework_MockObject_MockObject $event **/
        $event = $this->getMockBuilder(ConfigUpdateEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->method('isChanged')
            ->with(WebCatalogConfigChangeListener::WEB_CATALOG_CONFIGURATION_NAME)
            ->willReturn(true);
        
        $event->method('getScope')
            ->willReturn($scope);

        $event->method('getScopeId')
            ->willReturn($scopeId);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(ReindexationRequestEvent::EVENT_NAME, $expectedReindexationEvent);

        $this->websiteScopeChangeListener->onConfigurationUpdate($event);
    }
    
    public function onConfigurationUpdateProvider()
    {
        return [
            [MultiWebsiteLocalizationConfigListener::SUPPORTED_SCOPE, 41, new ReindexationRequestEvent([], [41])],
            ['_', '_', new ReindexationRequestEvent([], [])],
        ];
    }
}
