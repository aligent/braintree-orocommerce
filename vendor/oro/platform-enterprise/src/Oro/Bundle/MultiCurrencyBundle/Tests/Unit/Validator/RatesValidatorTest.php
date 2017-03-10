<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Validator;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\Rates;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\RatesValidator;

class RatesValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | TranslatorInterface
     */
    protected $translator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ExecutionContextInterface
     */
    protected $context;

    /**
     * @var ConstraintViolationBuilder
     */
    protected $constraintViolation;

    /**
     * @var RatesValidator
     */
    protected $validator;

    /**
     * @var Rates
     */
    protected $constraint;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->context = $this
            ->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->setMethods(['buildViolation'])
            ->getMock();

        $this->constraintViolation = $this
            ->getMockBuilder('Symfony\Component\Validator\Violation\ConstraintViolationBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setTranslationDomain', 'addViolation'])
            ->getMock();

        $this->constraintViolation
            ->expects($this->any())
            ->method('setTranslationDomain')
            ->with('messages')
            ->will($this->returnSelf());

        $this->constraintViolation
            ->expects($this->any())
            ->method('addViolation')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $this->translator = $this
            ->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->constraint = new Rates();
        $this->validator = new RatesValidator($this->translator);
        $this->validator->initialize($this->context);
    }


    public function testValidateBy()
    {
        $this->assertEquals(
            $this->constraint->validatedBy(),
            'oro_multi_currency.validator.rates'
        );
    }

    /**
     * @dataProvider validationProvider
     *
     * @param array       $value
     * @param string|null $message
     * @param string|null $direction
     * @param string|null $currency
     */
    public function testValidate(array $value, $message, $direction, $currency)
    {
        if (null === $direction) {
            $this->translator->expects($this->never())->method('trans');
        } else {
            $this->translator
                ->expects($this->once())
                ->method('trans')
                ->with($direction)
                ->willReturn($direction);

            $this->context
                ->expects($this->once())
                ->method('buildViolation')
                ->with($message, [
                    '%direction%' => $direction,
                    '%currency%'  => $currency,
                ])
                ->will($this->returnValue($this->constraintViolation));
        }

        $this->validator->validate($value, $this->constraint);
    }

    public function validationProvider()
    {
        return [
            'Valid rates' => [
                'value' => [
                    'USD' => [
                        'rateFrom' => 1,
                        'rateTo'   => 1
                    ],
                    'GBP' => [
                        'rateFrom' => 1.22,
                        'rateTo'   => 0.81
                    ]
                ],
                'message'   => null,
                'direction' => null,
                'currency'  => null
            ],
            'Empty exchange rate value' => [
                'value' => [
                    'USD' => [
                        'rateFrom' => 1,
                        'rateTo'   => 1
                    ],
                    'GBP' => [
                        'rateFrom' => 1.22,
                        'rateTo'   => ''
                    ]
                ],
                'message'   => 'oro.multi.currency.validator.rates.message.empty_value',
                'direction' => 'oro.multi.currency.system_configuration.currency_grid.rate_to',
                'currency'  => 'GBP'
            ],
            'Not numeric exchange rate value' => [
                'value' => [
                    'USD' => [
                        'rateFrom' => 1,
                        'rateTo'   => 1
                    ],
                    'GBP' => [
                        'rateFrom' => 'test',
                        'rateTo'   => 0.81
                    ]
                ],
                'message'   => 'oro.multi.currency.validator.rates.message.not_number',
                'direction' => 'oro.multi.currency.system_configuration.currency_grid.rate_from',
                'currency'  => 'GBP'
            ],
            'Less than zero exchange rate value' => [
                'value' => [
                    'USD' => [
                        'rateFrom' => -1,
                        'rateTo'   => 1
                    ],
                    'GBP' => [
                        'rateFrom' => 1.22,
                        'rateTo'   => 0.81
                    ]
                ],
                'message'   => 'oro.multi.currency.validator.rates.message.less_or_equal_to_zero',
                'direction' => 'oro.multi.currency.system_configuration.currency_grid.rate_from',
                'currency'  => 'USD'
            ]
        ];
    }
}
