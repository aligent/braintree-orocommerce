<?php

namespace Oro\Bundle\UserProBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\UserProBundle\Migrations\Schema\v1_1\AddFailedLoginColumns;
use Oro\Bundle\UserProBundle\Migrations\Schema\v1_1\AddPasswordExpiresAtColumn;
use Oro\Bundle\UserProBundle\Migrations\Schema\v1_1\AddPasswordHistoryTable;
use Oro\Bundle\UserProBundle\Migrations\Schema\v1_2\AddAuthenticationCodeTable;
use Oro\Bundle\UserProBundle\Migrations\Schema\v1_2\AddClientDataTable;
use Oro\Bundle\UserProBundle\Migrations\Schema\v1_3\AddLockedAuthStatusEnum;

class OroUserProBundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_3';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_access_role',
            'organization',
            'oro_organization',
            'id',
            [
                'extend' => [
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'is_extend' => true,
                ],
                'form' => [
                    'is_enabled' => true,
                    'form_type'  => 'oro_userpro_role_organization_select'
                ],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_FALSE
                ]
            ]
        );

        AddFailedLoginColumns::updateOroUserTable($schema);
        AddPasswordExpiresAtColumn::updateOroUserTable($schema);
        AddPasswordHistoryTable::createOroUserPasswordHistoryTable($schema);
        AddAuthenticationCodeTable::createOroUserAuthenticationCodeTable($schema);
        AddClientDataTable::createOroUserClientDataTable($schema);
        AddLockedAuthStatusEnum::addLockedStatusEnumAndValues($schema, $queries, $this->extendExtension);
    }
}
