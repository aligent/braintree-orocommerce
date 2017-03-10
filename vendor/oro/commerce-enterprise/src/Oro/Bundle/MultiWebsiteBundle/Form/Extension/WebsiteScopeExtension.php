<?php

namespace Oro\Bundle\MultiWebsiteBundle\Form\Extension;

use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteSelectType;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class WebsiteScopeExtension extends AbstractTypeExtension
{
    const SCOPE_FIELD = 'website';

    /**
     * @var string
     */
    protected $extendedType;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (array_key_exists(self::SCOPE_FIELD, $options['scope_fields'])) {
            $builder->add(
                self::SCOPE_FIELD,
                WebsiteSelectType::NAME,
                [
                    'label' => 'oro.website.entity_label',
                    'create_form_route' => null
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ScopeType::class;
    }
}
