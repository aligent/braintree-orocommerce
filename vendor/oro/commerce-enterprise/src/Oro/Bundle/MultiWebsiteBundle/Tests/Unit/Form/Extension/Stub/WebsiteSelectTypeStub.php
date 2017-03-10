<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Form\Extension\Stub;

use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteSelectTypeStub extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return WebsiteSelectType::NAME;
    }

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
    public function getParent()
    {
        return 'choice';
    }
}
