<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Validator;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\MultiCurrencyBundle\Provider\CurrencyCheckerProviderChain;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\CurrencyNotUsedInEntities;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\CurrencyNotUsedInEntitiesValidator;

class CurrencyNotUsedInEntitiesValidatorTest extends AbstractValidator
{
    /**
     * @var CurrencyNotUsedInEntitiesValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | CurrencyNotUsedInEntities
     */
    protected $constraint;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | CurrencyCheckerProviderChain
     */
    protected $currencyCheckerProviderChain;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | Organization
     */
    protected $organization;

    protected function setUp()
    {
        parent::setUp();
        $this->currencyCheckerProviderChain = $this
            ->getMockBuilder('Oro\Bundle\MultiCurrencyBundle\Provider\CurrencyCheckerProviderChain')
            ->disableOriginalConstructor()
            ->setMethods(['getEntityLabelsWithMissedCurrencies'])
            ->getMock();

        $this->organization = $this->createMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        $this->constraint = new CurrencyNotUsedInEntities();
        $this->validator = new CurrencyNotUsedInEntitiesValidator($this->currencyCheckerProviderChain);
        $this->validator->initialize($this->context);
    }

    public function testValidateBy()
    {
        $this->assertEquals(
            $this->constraint->validatedBy(),
            'oro_multi_currency.validator.currency_not_used_in_entities'
        );
    }

    /**
     * @dataProvider validationProvider
     *
     * @param array $value
     * @param array $currentValue
     * @param array $currenciesOnRemove
     * @param array $entityLabelsWithMissedCurrencies
     */
    public function testValidate(
        array $value,
        array $currentValue,
        array $currenciesOnRemove,
        array $entityLabelsWithMissedCurrencies
    ) {
        $constraint = new CurrencyNotUsedInEntities();
        $constraint->setValue($currentValue);

        if (empty($currenciesOnRemove)) {
            $this->currencyCheckerProviderChain->expects($this->never())->method('getEntityLabelsWithMissedCurrencies');
        } else {
            $this->currencyCheckerProviderChain
                ->expects($this->once())
                ->method('getEntityLabelsWithMissedCurrencies')
                ->with($currenciesOnRemove, $constraint->getOrganization())
                ->willReturn($entityLabelsWithMissedCurrencies);
        }

        if (empty($entityLabelsWithMissedCurrencies)) {
            $this->context
                ->expects($this->never())
                ->method('buildViolation');
        } else {
            $this->context
                ->expects($this->once())
                ->method('buildViolation')
                ->with($this->constraint->message, [
                    '%currencies%' => implode(', ', $currenciesOnRemove),
                    '%entities%'   => implode(', ', $entityLabelsWithMissedCurrencies)
                ])
                ->will($this->returnValue($this->constraintViolation));
        }

        $this->validator->validate($value, $constraint);
    }

    public function validationProvider()
    {
        return [
            'No currencies removed' => [
                'value'       => ['EUR', 'USD'],
                'currentValue' => ['EUR', 'USD'],
                'currencieOnRemove' => [],
                'entityLabelsWithMissedCurrencies' => []
            ],
            'Currency added' => [
                'value'       => ['EUR', 'USD', 'UAH'],
                'currentValue' => ['EUR', 'USD'],
                'currencieOnRemove' => [],
                'entityLabelsWithMissedCurrencies' => []
            ],
            'Currencies removed and no entities that use removed currencies' => [
                'value'       => ['EUR'],
                'currentValue' => ['EUR', 'USD'],
                'currencieOnRemove' => ['USD'],
                'entityLabelsWithMissedCurrencies' => []
            ],
            'Currencies removed and entity "Test" that use removed currencies' => [
                'value'       => ['EUR'],
                'currentValue' => ['EUR', 'USD'],
                'currencieOnRemove' => ['USD'],
                'entityLabelsWithMissedCurrencies' => ['Test']
            ]
        ];
    }
}
