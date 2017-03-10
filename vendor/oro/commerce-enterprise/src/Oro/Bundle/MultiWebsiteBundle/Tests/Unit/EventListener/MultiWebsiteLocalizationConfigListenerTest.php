<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\MultiWebsiteBundle\EventListener\MultiWebsiteLocalizationConfigListener;
use Oro\Bundle\WebsiteSearchBundle\Event\ReindexationRequestEvent;
use Oro\Bundle\WebsiteSearchBundle\EventListener\WebsiteLocalizationConfigListener;

class MultiWebsiteLocalizationConfigListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderForReindexationRequestEventContainsCurrentWebsiteId
     * @param string|null $scope
     * @param int|null    $scopeId
     * @param array       $websitesIds
     */
    public function testReindexationRequestEventContainsCurrentWebsiteId($scope, $scopeId, array $websitesIds)
    {
        $listener = new MultiWebsiteLocalizationConfigListener($this->getEventDispatcherMock(
            $this->once(),
            $websitesIds
        ));

        $listener->onLocalizationSettingsChange(new ConfigUpdateEvent([
            WebsiteLocalizationConfigListener::CONFIG_LOCALIZATION_DEFAULT => 1,
        ], $scope, $scopeId));
    }

    public function testFullReindexationTriggeredWhenNoWebsiteIdScopeProvided()
    {
        $listener = new MultiWebsiteLocalizationConfigListener($this->getEventDispatcherMock($this->once()));
        $listener->onLocalizationSettingsChange(new ConfigUpdateEvent([
            WebsiteLocalizationConfigListener::CONFIG_LOCALIZATION_DEFAULT => 1,
        ]));
    }

    /**
     * @return array
     */
    public function dataProviderForReindexationRequestEventContainsCurrentWebsiteId()
    {
        return [
            [
                MultiWebsiteLocalizationConfigListener::SUPPORTED_SCOPE, 1, [1]
            ],
            [
                MultiWebsiteLocalizationConfigListener::SUPPORTED_SCOPE, null, []
            ],
            [
                null, 1, []
            ],
            [
                null, null, []
            ],
            [
                'not-existing', null, []
            ],
        ];
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $expectCalled
     * @param array                                            $websitesIds
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    private function getEventDispatcherMock(
        \PHPUnit_Framework_MockObject_Matcher_Invocation $expectCalled,
        array $websitesIds = []
    ) {
        $eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $eventDispatcher
            ->expects($expectCalled)
            ->method('dispatch')
            ->with(
                ReindexationRequestEvent::EVENT_NAME,
                $this->callback(function ($reindexationEvent) use ($websitesIds) {
                    /** @var ReindexationRequestEvent $reindexationEvent */
                    return $reindexationEvent->getWebsitesIds() === $websitesIds;
                })
            );

        return $eventDispatcher;
    }
}
