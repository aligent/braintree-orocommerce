<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Form\Extension\Stub;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteType;

class WebsiteTypeStub extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return WebsiteType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['label' => 'oro.website.name.label']);
    }
}
