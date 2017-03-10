<?php

namespace Oro\Bundle\LDAPBundle\Twig;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityExtendBundle\Twig\AbstractDynamicFieldsExtension;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Class DynamicFieldsExtension
 *
 * Decorator of dynamic fields twig extension.
 *
 * @package Oro\Bundle\LDAPBundle\Twig
 */
class LdapDynamicFieldsExtension extends AbstractDynamicFieldsExtension
{
    /** @var AbstractDynamicFieldsExtension */
    private $baseExtension;

    /** @var SecurityFacade */
    private $securityFacade;

    /**
     * @param AbstractDynamicFieldsExtension $baseExtension
     * @param SecurityFacade             $securityFacade
     */
    public function __construct(AbstractDynamicFieldsExtension $baseExtension, SecurityFacade $securityFacade)
    {
        $this->baseExtension = $baseExtension;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param object      $entity
     * @param null|string $entityClass
     *
     * @return array
     */
    public function getFields($entity, $entityClass = null)
    {
        $fields = $this->baseExtension->getFields($entity, $entityClass);
        if (isset($fields['ldap_distinguished_names']) && !$this->securityFacade->isGranted('ROLE_ADMINISTRATOR')) {
            unset($fields['ldap_distinguished_names']);
        }
        return $fields;
    }

    /**
     * @param object $entity
     * @param FieldConfigModel $field
     * @return array
     */
    public function getField($entity, FieldConfigModel $field)
    {
        return $this->baseExtension->getField($entity, $field);
    }
}
