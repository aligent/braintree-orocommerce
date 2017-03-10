<?php

namespace Oro\Bundle\UserProBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserProBundle\Security\AuthStatus;

class AddLockedAuthStatusEnum implements Migration, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension $extendExtension */
    protected $extendExtension;

    /**
     * @param Schema $schema
     * @param ExtendExtension $extendExtension
     */
    public static function addLockedStatusEnum(Schema $schema, ExtendExtension $extendExtension)
    {
        $enumTable = $schema->getTable($extendExtension->getNameGenerator()->generateEnumTableName('auth_status'));

        $options = new OroOptions();
        $options->set(
            'enum',
            'immutable_codes',
            [
                UserManager::STATUS_ACTIVE,
                UserManager::STATUS_EXPIRED,
                AuthStatus::LOCKED,
            ]
        );

        $enumTable->addOption(OroOptions::KEY, $options);
    }

    /**
     * @param QueryBag $queries
     * @param ExtendExtension $extendExtension
     */
    public static function addEnumValues(QueryBag $queries, ExtendExtension $extendExtension)
    {
        $queries->addPostQuery(new InsertLockedStatusQuery($extendExtension));
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @param ExtendExtension $extendExtension
     */
    public static function addLockedStatusEnumAndValues(
        Schema $schema,
        QueryBag $queries,
        ExtendExtension $extendExtension
    ) {
        self::addLockedStatusEnum($schema, $extendExtension);
        self::addEnumValues($queries, $extendExtension);
    }

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
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addLockedStatusEnumAndValues($schema, $queries, $this->extendExtension);
    }
}
