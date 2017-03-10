<?php

namespace Oro\Bundle\OrganizationProBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

/**
 * This form type used in system access mode to display record's organization on create/update form
 *
 * Class OrganizationLabelType
 * @package Oro\Bundle\OrganizationProBundle\Form\Type
 */
class OrganizationLabelType extends AbstractType
{
    const NAME = 'oro_organizationpro_label';

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
