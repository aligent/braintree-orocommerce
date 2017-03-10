<?php

namespace Oro\Bundle\UserProBundle\Migrations\Schema\v1_3;

use Oro\Bundle\UserProBundle\Security\AuthStatus;
use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;

class InsertLockedStatusQuery extends ParametrizedMigrationQuery
{
    /** @var $extendExtension */
    protected $extendExtension;

    /**
     * @param ExtendExtension $extendExtension
     */
    public function __construct(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info('Insert locked user auth_status.');
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('auth_status');

        $sql = 'INSERT INTO %s (id, name, priority, is_default) VALUES (:id, :name, :priority, :is_default)';
        $sql = sprintf($sql, $tableName);

        $statuses = [
            [
                ':id' => AuthStatus::LOCKED,
                ':name' => 'Locked',
                ':priority' => 3,
                ':is_default' => false,
            ],
        ];

        $types = [
            'id' => Type::STRING,
            'name' => Type::STRING,
            'priority' => Type::INTEGER,
            'is_default' => Type::BOOLEAN,
        ];

        foreach ($statuses as $status) {
            $this->logQuery($logger, $sql, $status, $types);
            if (!$dryRun) {
                $this->connection->executeUpdate($sql, $status, $types);
            }
        }
    }
}
