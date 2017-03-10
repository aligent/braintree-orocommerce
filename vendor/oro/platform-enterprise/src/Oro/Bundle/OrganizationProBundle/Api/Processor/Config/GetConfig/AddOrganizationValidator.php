<?php

namespace Oro\Bundle\OrganizationProBundle\Api\Processor\Config\GetConfig;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Processor\Config\ConfigContext;
use Oro\Bundle\ApiBundle\Request\ApiActions;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\ApiBundle\Util\ValidationHelper;
use Oro\Bundle\OrganizationProBundle\Validator\Constraints\Organization;
use Oro\Bundle\SecurityProBundle\Owner\Metadata\OwnershipMetadataProProvider;

/**
 * For "create" action adds NotNull validation constraint for "organization" field.
 * Adds NotBlank validation constraint for "organization" field.
 * Adds Organization validation constraint for the entity.
 */
class AddOrganizationValidator implements ProcessorInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var OwnershipMetadataProProvider */
    protected $ownershipMetadataProvider;

    /** @var ValidationHelper */
    protected $validationHelper;

    /**
     * @param DoctrineHelper               $doctrineHelper
     * @param OwnershipMetadataProProvider $ownershipMetadataProvider
     * @param ValidationHelper             $validationHelper
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        OwnershipMetadataProProvider $ownershipMetadataProvider,
        ValidationHelper $validationHelper
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
        $this->validationHelper = $validationHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var ConfigContext $context */

        $entityClass = $context->getClassName();
        if (!$this->doctrineHelper->isManageableEntityClass($entityClass)) {
            // only manageable entities are supported
            return;
        }

        $this->addValidators($context->getResult(), $entityClass);
    }

    /**
     * @param EntityDefinitionConfig $definition
     * @param string                 $entityClass
     */
    protected function addValidators(EntityDefinitionConfig $definition, $entityClass)
    {
        $fieldName = $this->ownershipMetadataProvider->getMetadata($entityClass)->getGlobalOwnerFieldName();
        if (!$fieldName) {
            return;
        }
        $field = $definition->findField($fieldName, true);
        if (null === $field) {
            return;
        }

        // add organization validator
        if (!$this->validationHelper->hasValidationConstraintForClass($entityClass, Organization::class)) {
            $definition->addFormConstraint(new Organization());
        }
    }
}
