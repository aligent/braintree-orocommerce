<?php

namespace Oro\Bundle\MultiWebsiteBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteSelectType extends AbstractType
{
    const NAME = 'oro_multiwebsite_website_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'grid_name' => 'website-select-grid',
                'autocomplete_alias' => 'oro_website',
                'create_form_route' => 'oro_multiwebsite_create',
                'configs' => [
                    'placeholder' => 'oro.multiwebsite.form.website.choose',
                ]
            ]
        );
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

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::NAME;
    }
}
