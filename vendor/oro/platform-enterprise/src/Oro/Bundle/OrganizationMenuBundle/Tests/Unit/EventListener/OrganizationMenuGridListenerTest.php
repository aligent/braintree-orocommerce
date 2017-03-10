<?php

namespace Oro\Bundle\OrganizationMenuBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationMenuBundle\EventListener\OrganizationMenuGridListener;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;

use Oro\Component\Testing\Unit\EntityTrait;

class OrganizationMenuGridListenerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    const ORGANIZATION_ID = 5;
    const SCOPE_ID = '10';
    const SCOPE_TYPE = 'custom_scope_type';

    public function testOnPreBuild()
    {
        $organization = $this->getEntity(Organization::class, ['id' => self::ORGANIZATION_ID]);
        $scope = $this->getEntity(Scope::class, ['id' => self::SCOPE_ID]);
        $scopeManager = $this->getMockBuilder(ScopeManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeManager->expects($this->once())
            ->method('findOrCreate')
            ->with(self::SCOPE_TYPE, ['organization' => $organization])
            ->willReturn($scope);

        $gridConfig = DatagridConfiguration::create(
            [
                'properties' => [],
                'actions' => []
            ]
        );

        $params = new ParameterBag();
        $params->set('organization', $organization);
        $event = new PreBuild($gridConfig, $params);
        $listener = new OrganizationMenuGridListener($scopeManager);
        $listener->setScopeType(self::SCOPE_TYPE);
        $listener->onPreBefore($event);

        $this->assertEquals(
            self::SCOPE_ID,
            $gridConfig->offsetGetByPath('[properties][view_link][direct_params][scopeId]')
        );
    }
}
