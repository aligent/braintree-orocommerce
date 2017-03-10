<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Validator;

use Oro\Bundle\MultiCurrencyBundle\Provider\ConfigDependencyInterface;
use Oro\Bundle\MultiCurrencyBundle\Provider\DependentConfigProvider;
use Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Stub\StubConfigDependency;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\CurrencyNotUsedInConfig;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\CurrencyNotUsedInConfigValidator;

class CurrencyNotUsedInConfigValidatorTest extends AbstractValidator
{
    /** @var CurrencyNotUsedInConfig */
    private $constraint;

    /** @var CurrencyNotUsedInConfigValidator */
    private $validator;

    protected function setUp()
    {
        parent::setUp();

        $dependentConfigProvider = new DependentConfigProvider();
        $dependentConfigProvider->addDependency(new StubConfigDependency(['USD', 'EUR']));

        $this->constraint = new CurrencyNotUsedInConfig();
        $this->validator = new CurrencyNotUsedInConfigValidator($dependentConfigProvider);
        $this->validator->initialize($this->context);
    }

    public function testValidatorConnectedToConstraint()
    {
        $this->assertEquals(
            $this->constraint->validatedBy(),
            'oro_multi_currency.validator.currency_not_used_in_config'
        );
    }

    public function testSimpleValidationCase()
    {
        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate(['USD', 'EUR'], $this->constraint);
    }

    public function testDeleteCurrencyWhichInOtherConfig()
    {
        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->will($this->returnValue($this->constraintViolation));

        $this->constraintViolation
            ->expects($this->once())
            ->method('addViolation');

        $this->validator->validate(['USD'], $this->constraint);
    }
}
