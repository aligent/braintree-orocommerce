<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\MultiWebsiteBundle\Form\Extension\WebsiteScopeExtension;
use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteSelectType;
use Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Form\Extension\Stub\WebsiteSelectTypeStub;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeType;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class WebsiteScopeExtensionTest extends FormIntegrationTestCase
{
    /**
     * @var WebsiteScopeExtension
     */
    protected $websiteScopeExtension;

    /**
     * @var ScopeManager|\PHPUnit_Framework_MockObject_MockObject $scopeManager
     */
    protected $scopeManager;

    protected function setUp()
    {
        $this->websiteScopeExtension = new WebsiteScopeExtension();

        parent::setUp();
    }

    public function testBuildForm()
    {
        $this->scopeManager->expects($this->once())
            ->method('getScopeEntities')
            ->with('web_content')
            ->willReturn(['website' => Website::class]);

        $form = $this->factory->create(
            ScopeType::NAME,
            null,
            [ScopeType::SCOPE_TYPE_OPTION => 'web_content']
        );

        $this->assertTrue($form->has('website'));
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(ScopeType::class, $this->websiteScopeExtension->getExtendedType());
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $this->scopeManager = $this->getMockBuilder(ScopeManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            new PreloadedExtension(
                [
                    ScopeType::NAME => new ScopeType($this->scopeManager),
                    WebsiteSelectType::NAME => new WebsiteSelectTypeStub(),
                ],
                [
                    ScopeType::NAME => [$this->websiteScopeExtension],
                ]
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
