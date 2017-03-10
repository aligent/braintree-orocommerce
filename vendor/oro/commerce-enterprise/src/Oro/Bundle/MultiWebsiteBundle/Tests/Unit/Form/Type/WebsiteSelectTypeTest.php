<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteSelectType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebsiteSelectType
     */
    protected $type;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->type = new WebsiteSelectType();
    }

    public function testGetName()
    {
        $this->assertEquals(WebsiteSelectType::NAME, $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::NAME, $this->type->getParent());
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|\PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(
                function (array $options) {
                    $this->assertArrayHasKey('autocomplete_alias', $options);
                    $this->assertArrayHasKey('create_form_route', $options);
                    $this->assertArrayHasKey('configs', $options);
                    $this->assertEquals('oro_website', $options['autocomplete_alias']);
                    $this->assertEquals('oro_multiwebsite_create', $options['create_form_route']);
                    $this->assertEquals(['placeholder' => 'oro.multiwebsite.form.website.choose'], $options['configs']);
                }
            );

        $this->type->configureOptions($resolver);
    }
}
