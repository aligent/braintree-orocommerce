<?php

namespace Oro\Bundle\OrganizationProBundle\Tests\Unit\Api\Processor\Config\GetConfig;

use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Request\ApiActions;
use Oro\Bundle\ApiBundle\Tests\Unit\Processor\Config\ConfigProcessorTestCase;
use Oro\Bundle\OrganizationProBundle\Api\Processor\Config\GetConfig\AddOrganizationValidator;
use Oro\Bundle\OrganizationProBundle\Validator\Constraints\Organization;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;

class AddOrganizationValidatorTest extends ConfigProcessorTestCase
{
    /** @var AddOrganizationValidator */
    protected $processor;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $doctrineHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $ownershipMetadataProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $validationHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\ApiBundle\Util\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->ownershipMetadataProvider = $this
            ->getMockBuilder('Oro\Bundle\SecurityProBundle\Owner\Metadata\OwnershipMetadataProProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationHelper = $this->getMockBuilder('Oro\Bundle\ApiBundle\Util\ValidationHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new AddOrganizationValidator(
            $this->doctrineHelper,
            $this->ownershipMetadataProvider,
            $this->validationHelper
        );
    }

    public function testProcessForNotManageableEntity()
    {
        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn(false);
        $this->ownershipMetadataProvider->expects($this->never())
            ->method('getMetadata');

        $this->processor->process($this->context);
    }

    public function testProcessForCreateAction()
    {
        $config = [
            'fields' => [
                'org' => null,
            ]
        ];
        $ownershipMetadata = new OwnershipMetadata('USER', 'owner', 'owner', 'org', 'org');

        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn(true);
        $this->ownershipMetadataProvider->expects($this->once())
            ->method('getMetadata')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn($ownershipMetadata);

        /** @var EntityDefinitionConfig $configObject */
        $configObject = $this->createConfigObject($config);
        $this->context->setResult($configObject);
        $this->context->setTargetAction(ApiActions::CREATE);
        $this->processor->process($this->context);

        $this->assertEquals(
            ['constraints' => [new Organization()]],
            $configObject->getFormOptions()
        );
    }

    public function testProcess()
    {
        $config = [
            'fields' => [
                'org' => null,
            ]
        ];
        $ownershipMetadata = new OwnershipMetadata('USER', 'owner', 'owner', 'org', 'org');

        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn(true);
        $this->ownershipMetadataProvider->expects($this->once())
            ->method('getMetadata')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn($ownershipMetadata);

        /** @var EntityDefinitionConfig $configObject */
        $configObject = $this->createConfigObject($config);
        $this->context->setResult($configObject);
        $this->processor->process($this->context);

        $this->assertEquals(
            ['constraints' => [new Organization()]],
            $configObject->getFormOptions()
        );
    }

    public function testProcessForRenamedOrganizationField()
    {
        $config = [
            'fields' => [
                'org1' => ['property_path' => 'org'],
            ]
        ];
        $ownershipMetadata = new OwnershipMetadata('USER', 'owner', 'owner', 'org', 'org');

        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn(true);
        $this->ownershipMetadataProvider->expects($this->once())
            ->method('getMetadata')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn($ownershipMetadata);

        /** @var EntityDefinitionConfig $configObject */
        $configObject = $this->createConfigObject($config);
        $this->context->setResult($configObject);
        $this->processor->process($this->context);

        $this->assertEquals(
            ['constraints' => [new Organization()]],
            $configObject->getFormOptions()
        );
    }

    public function testProcessWithoutOrganizationField()
    {
        $config = [
            'fields' => [
                'someField' => null,
            ]
        ];
        $ownershipMetadata = new OwnershipMetadata('USER', 'owner', 'owner', 'org', 'org');

        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn(true);
        $this->ownershipMetadataProvider->expects($this->once())
            ->method('getMetadata')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn($ownershipMetadata);

        /** @var EntityDefinitionConfig $configObject */
        $configObject = $this->createConfigObject($config);
        $this->context->setResult($configObject);
        $this->processor->process($this->context);

        $this->assertEmpty($configObject->getFormOptions());
    }

    public function testProcessWhenConstraintsAlreadyExist()
    {
        $config = [
            'fields' => [
                'org' => null,
            ]
        ];
        $ownershipMetadata = new OwnershipMetadata('USER', 'owner', 'owner', 'org', 'org');

        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn(true);
        $this->ownershipMetadataProvider->expects($this->once())
            ->method('getMetadata')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn($ownershipMetadata);
        $this->validationHelper->expects($this->once())
            ->method('hasValidationConstraintForClass')
            ->with(
                self::TEST_CLASS_NAME,
                'Oro\Bundle\OrganizationProBundle\Validator\Constraints\Organization'
            )
            ->willReturn(true);

        /** @var EntityDefinitionConfig $configObject */
        $configObject = $this->createConfigObject($config);
        $this->context->setResult($configObject);
        $this->processor->process($this->context);

        $this->assertNull($configObject->getFormOptions());
        $this->assertNull($configObject->getField('org')->getFormOptions());
    }

    public function testProcessWhenConstraintsAlreadyExistAndRenamedOrganizationField()
    {
        $config = [
            'fields' => [
                'org1' => ['property_path' => 'org'],
            ]
        ];
        $ownershipMetadata = new OwnershipMetadata('USER', 'owner', 'owner', 'org', 'org');

        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn(true);
        $this->ownershipMetadataProvider->expects($this->once())
            ->method('getMetadata')
            ->with(self::TEST_CLASS_NAME)
            ->willReturn($ownershipMetadata);
        $this->validationHelper->expects($this->once())
            ->method('hasValidationConstraintForClass')
            ->with(
                self::TEST_CLASS_NAME,
                'Oro\Bundle\OrganizationProBundle\Validator\Constraints\Organization'
            )
            ->willReturn(true);

        /** @var EntityDefinitionConfig $configObject */
        $configObject = $this->createConfigObject($config);
        $this->context->setResult($configObject);
        $this->processor->process($this->context);

        $this->assertNull($configObject->getFormOptions());
        $this->assertNull($configObject->getField('org1')->getFormOptions());
    }
}
