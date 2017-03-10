<?php

namespace Oro\Bundle\WarehouseBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\NoteBundle\Migration\UpdateNoteAssociationKindForRenamedEntitiesMigration;

class MigrateNotes extends UpdateNoteAssociationKindForRenamedEntitiesMigration
{
    /**
     * {@inheritdoc}
     */
    protected function getRenamedEntitiesNames(Schema $schema)
    {
        return [
            'Oro\Bundle\WarehouseBundle\Entity\Warehouse' => 'OroB2B\Bundle\WarehouseBundle\Entity\Warehouse'
        ];
    }
}
