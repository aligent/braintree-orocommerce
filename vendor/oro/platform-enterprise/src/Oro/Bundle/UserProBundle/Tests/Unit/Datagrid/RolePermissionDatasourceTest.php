<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserProBundle\Datagrid\RolePermissionDatasource;
use Oro\Bundle\UserProBundle\Tests\Unit\Fixture\Entity\Organization;
use Oro\Bundle\UserProBundle\Tests\Unit\Fixture\Entity\Role;
use Oro\Bundle\UserBundle\Model\PrivilegeCategory;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class RolePermissionDatasourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $permissionManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $aclRoleHandler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoryProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $configEntityManager;

    /** @var RolePermissionDatasource */
    protected $datasource;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Config */
    protected $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject|SecurityFacade */
    protected $securityFacade;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ConfigProvider */
    protected $configProvider;

    protected function setUp()
    {
        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $this->permissionManager = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Permission\PermissionManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->aclRoleHandler = $this->getMockBuilder('Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler')
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryProvider = $this->getMockBuilder('Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configEntityManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->datasource = new RolePermissionDatasource(
            $this->translator,
            $this->permissionManager,
            $this->aclRoleHandler,
            $this->categoryProvider,
            $this->configEntityManager
        );

        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();

        $this->datasource->setSecurityFacade($this->securityFacade);
    }

    /**
     * Test grid results with organizations restrictions
     *
     * @param Organization $organization
     * @param Role $role
     * @param array $expected
     * @dataProvider resultsDataProvider
     */
    public function testGetResultsWithOrganizations($organization, $role, $expected)
    {
        $this->securityFacade->expects($this->any())
            ->method('getOrganizationId')
            ->willReturn($organization->getId());

        $this->securityFacade->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $entityConfig1 = new Config(new EntityConfigId('organization'), [
            'applicable' => [
                'all' => false,
                'selective' => [1]
            ]
        ]);

        $entityConfig2 = new Config(new EntityConfigId('organization'), [
            'applicable' => [
                'all' => false,
                'selective' => [2]
            ]
        ]);

        $entityConfig3 = new Config(new EntityConfigId('organization'), [
            'applicable' => [
                'all' => true,
                'selective' => []
            ]
        ]);

        $this->configEntityManager->expects($this->any())
            ->method('getEntityConfig')
            ->will($this->onConsecutiveCalls($entityConfig1, $entityConfig2, $entityConfig3));

        $parameters = new ParameterBag();
        $parameters->add(['role' => $role]);

        $datagridConfig = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        $grid = new Datagrid('test', $datagridConfig, $parameters);

        $this->datasource->process($grid, []);
        $privilege1 = new AclPrivilege();
        $privilege1->setIdentity(new AclPrivilegeIdentity('entity:Acme\Test1Entity', 'test entity'));
        $privilege1->setCategory('testCategory');

        $privilege2 = new AclPrivilege();
        $privilege2->setIdentity(new AclPrivilegeIdentity('entity:Acme\Test2Entity', 'test2 entity'));
        $privilege2->setCategory('testCategory');

        $privilege3 = new AclPrivilege();
        $privilege3->setIdentity(new AclPrivilegeIdentity('entity:Acme\Test3Entity', 'test3 entity'));
        $privilege3->setCategory('testCategory');

        $privileges = new ArrayCollection([
            'entity' => new ArrayCollection([
                $privilege1,
                $privilege2,
                $privilege3
            ])
        ]);

        $this->aclRoleHandler->expects($this->once())
            ->method('getAllPrivileges')
            ->willReturn($privileges);

        $category = new PrivilegeCategory('testCategory', 'testCategory', true, 1);
        $this->categoryProvider->expects($this->once())
            ->method('getPermissionCategories')
            ->willReturn([$category]);

        $result = $this->datasource->getResults();

        $expectedEntities = [];
        foreach ($result as $record) {
            /** @var $record ResultRecord */
            $expectedEntities[] = $record->getValue('identity');
        }

        $this->assertEquals($expected, $expectedEntities);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function resultsDataProvider()
    {
        $org1 = new Organization();
        $org1->setId(1);

        $org2 = new Organization();
        $org2->setId(2);

        $org3 = new Organization();
        $org3->setId(3);
        $org3->setIsGlobal(true);

        $role1 = new Role();
        $role1->setOrganization($org1);

        $role2 = new Role();
        $role2->setOrganization($org2);

        $role3 = new Role();
        $role3->setOrganization(null);

        return [
            'Role1 in global Organization3 - Test1Entity and Test3Entity should be visible' => [
                'organization' => $org3,
                'role'         => $role1,
                'expected'     => [
                    'entity:Acme\Test1Entity',
                    'entity:Acme\Test3Entity',
                ]
            ],
            'Role2 in global Organization3 - Test2Entity and Test3Entity should be visible' => [
                'organization' => $org3,
                'role'         => $role2,
                'expected'     => [
                    'entity:Acme\Test2Entity',
                    'entity:Acme\Test3Entity',
                ]
            ],
            'System Role3 in global Organization3 - all entities should be visible'         => [
                'organization' => $org3,
                'role'         => $role3,
                'expected'     => [
                    'entity:Acme\Test1Entity',
                    'entity:Acme\Test2Entity',
                    'entity:Acme\Test3Entity',
                ]
            ],
            'Role1 in Organization1 - Test1Entity and Test3Entity are visible'              => [
                'organization' => $org1,
                'role'         => $role1,
                'expected'     => [
                    'entity:Acme\Test1Entity',
                    'entity:Acme\Test3Entity',
                ]
            ],
            'System Role3 in Organization1 - Test1Entity and Test3Entity should be shown'   => [
                'organization' => $org1,
                'role'         => $role3,
                'expected'     => [
                    'entity:Acme\Test1Entity',
                    'entity:Acme\Test3Entity',
                ]
            ],
            'Role2 in Organization2 - Test2Entity and Test3Entity should be visible'        => [
                'organization' => $org2,
                'role'         => $role2,
                'expected'     => [
                    'entity:Acme\Test2Entity',
                    'entity:Acme\Test3Entity',
                ]
            ],
            'System Role3 in Organization2 - Test2Entity and Test3Entity should be visible' => [
                'organization' => $org2,
                'role'         => $role3,
                'expected'     => [
                    'entity:Acme\Test2Entity',
                    'entity:Acme\Test3Entity',
                ]
            ],
        ];
    }
}
